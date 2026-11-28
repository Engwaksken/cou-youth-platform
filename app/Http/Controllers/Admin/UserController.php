<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\OrganisationRole;
use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Support\AssignableRoles;
use App\Support\ChurchOfUgandaDioceses;
use App\Support\UserProfilePhoto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class UserController extends Controller
{
    use GuardsSuperAdminMutations;

    /** dompdf renders large tables slowly; CSV has no limit. */
    public const PDF_ROW_LIMIT = 2000;

    public function index(Request $request): View|AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);
        $dioceseId = $request->integer('diocese_id');
        $users = $this->filteredUsers($dioceseId)->paginate(20)->withQueryString();
        if ($request->expectsJson()) {
            return UserResource::collection($users);
        }
        $dioceses = ChurchOfUgandaDioceses::options();
        $roles = AssignableRoles::options();
        $stats = $this->stats();

        return view('admin.users.index', compact('users', 'dioceses', 'dioceseId', 'roles', 'stats'));
    }

    public function store(StoreUserRequest $request): UserResource|JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? null;
        unset($data['role']);

        $user = $this->withinSuperAdminMutationLock(function () use ($request, $data, $role): User {
            $user = User::create($data);

            if ($role !== null) {
                $unitId = $request->roleUnitId();
                $catalogRoleId = AssignableRoles::catalogRoleId($role, $unitId);
                if ($catalogRoleId !== null) {
                    OrganisationRole::query()->whereKey($catalogRoleId)->lockForUpdate()->firstOrFail();
                }

                UserOrganisationRole::create([
                    'user_id' => $user->id,
                    'organisation_unit_id' => $unitId,
                    'organisation_role_id' => $catalogRoleId,
                    'role' => $role,
                    'is_active' => true,
                ]);
            }

            return $user;
        });

        return $request->expectsJson() ? (new UserResource($user))->response()->setStatusCode(201) : back()->with('success', 'User created.');
    }

    public function show(Request $request, User $user): UserResource|View
    {
        $this->authorize('view', $user);
        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        $user->load(['diocese', 'youthProfile.diocese', 'organisationRoles.organisationUnit']);

        return view('admin.users.show', [
            'user' => $user,
            'dioceses' => ChurchOfUgandaDioceses::options(),
            'isSelf' => false,
            'formAction' => route('admin.users.update', $user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource|RedirectResponse
    {
        $this->authorize('update', $user);
        UserProfilePhoto::updateUser($user, $request->validated());

        return $request->expectsJson() ? new UserResource($user->refresh()) : back()->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): Response|RedirectResponse
    {
        $this->authorize('delete', $user);
        $this->withinSuperAdminMutationLock(function () use ($user): void {
            $user->refresh();
            $this->guardLastSuperAdminUserDeletion($user);
            $user->delete();
        });
        UserProfilePhoto::delete($user);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        // Deleting from the profile page cannot go "back" to a page that no longer exists.
        return redirect()->route('admin.users.index')->with('success', 'User removed.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', User::class);
        $query = $this->filteredUsers($request->integer('diocese_id'));

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names correctly.
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Location', 'Diocese', 'Roles', 'Joined']);
            $query->chunk(500, function ($users) use ($out): void {
                foreach ($users as $user) {
                    fputcsv($out, array_map([self::class, 'csvSafe'], $this->exportRow($user)));
                }
            });
            fclose($out);
        }, 'users-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', User::class);
        $dioceseId = $request->integer('diocese_id');
        $query = $this->filteredUsers($dioceseId);
        $total = (clone $query)->count();
        $rows = $query->limit(self::PDF_ROW_LIMIT)->get()->map(fn (User $user) => $this->exportRow($user));

        return Pdf::loadView('admin.users.export-pdf', [
            'rows' => $rows,
            'total' => $total,
            'limit' => self::PDF_ROW_LIMIT,
            'stats' => $this->stats(),
            'dioceseName' => $dioceseId > 0 ? ChurchOfUgandaDioceses::options()->firstWhere('id', $dioceseId)?->name : null,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('users-'.now()->format('Y-m-d').'.pdf');
    }

    private function filteredUsers(int $dioceseId): Builder
    {
        return User::query()
            ->with(['diocese', 'youthProfile.diocese', 'organisationRoles' => fn ($roles) => $roles->where('is_active', true)])
            ->when($dioceseId > 0, fn ($query) => $query->where(fn ($match) => $match
                ->where('diocese_id', $dioceseId)
                ->orWhereHas('youthProfile', fn ($profile) => $profile->where('diocese_id', $dioceseId))))
            ->orderBy('id');
    }

    /** @return array{total: int, staff: int, youth: int, new: int} */
    private function stats(): array
    {
        return [
            'total' => User::query()->count(),
            'staff' => User::query()->whereHas('organisationRoles', fn ($roles) => $roles->where('is_active', true)->whereIn('role', User::CMS_ROLES))->count(),
            'youth' => User::query()->whereHas('youthProfile')->count(),
            'new' => User::query()->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /** @return list<string> */
    private function exportRow(User $user): array
    {
        return [
            (string) $user->id,
            (string) $user->name,
            (string) $user->email,
            (string) $user->phone,
            (string) $user->location,
            (string) ($user->diocese?->name ?? $user->youthProfile?->diocese?->name),
            $user->organisationRoles->pluck('role')->unique()->map(fn ($role) => AssignableRoles::label((string) $role))->implode(', '),
            (string) $user->created_at?->format('Y-m-d'),
        ];
    }

    /** Stop spreadsheet apps from running cell values as formulas. */
    private static function csvSafe(string $value): string
    {
        if ($value === '' || preg_match(StoreUserRequest::PHONE_PATTERN, $value) === 1) {
            return $value; // Phone numbers like +256… are safe and should stay readable.
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
