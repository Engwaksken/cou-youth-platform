<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserOrganisationRoleRequest;
use App\Http\Requests\Admin\UpdateUserOrganisationRoleRequest;
use App\Http\Resources\Admin\UserOrganisationRoleResource;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\OrganisationRole;
use App\Models\OrganisationUnit;
use Illuminate\Http\RedirectResponse;

final class UserOrganisationRoleController extends Controller
{
    use GuardsSuperAdminMutations;

    public function index(Request $request): View|AnonymousResourceCollection
    {
        $this->authorize('viewAny', UserOrganisationRole::class);
        $assignments = UserOrganisationRole::query()->with(['user', 'organisationUnit'])->orderBy('id')->paginate(20);
        if ($request->expectsJson()) return UserOrganisationRoleResource::collection($assignments);
        $roles = OrganisationRole::query()
            ->when($request->filled('organisation_unit_id'), function ($query) use ($request): void {
                $query->where(function ($query) use ($request): void {
                    $query->whereNull('organisation_unit_id')
                        ->orWhere('organisation_unit_id', $request->input('organisation_unit_id'));
                });
            })
            ->with('organisationUnit')
            ->orderBy('name')
            ->get();

        return view('admin.user-organisation-roles.index', [
            'assignments' => $assignments,
            'users' => User::query()->orderBy('name')->get(),
            'organisationUnits' => OrganisationUnit::query()->orderBy('name')->get(),
            'roles' => $roles,
        ]);
    }
    public function store(StoreUserOrganisationRoleRequest $request): JsonResponse|RedirectResponse|UserOrganisationRoleResource
    {
        $this->authorize('create', UserOrganisationRole::class);
        $data = $request->validated();
        unset($data['permissions']); // Catalog permissions are authoritative; legacy roles retain model behavior.
        $assignment = DB::transaction(function () use ($data): UserOrganisationRole {
            $this->lockSuperAdminMutationSet();
            return UserOrganisationRole::create($data);
        });
        return $request->expectsJson() ? (new UserOrganisationRoleResource($assignment))->response()->setStatusCode(201) : back()->with('success', 'Role assignment created.');
    }
    public function update(UpdateUserOrganisationRoleRequest $request, UserOrganisationRole $userOrganisationRole): JsonResponse|UserOrganisationRoleResource|RedirectResponse
    {
        $this->authorize('update', $userOrganisationRole);
        $data = $request->validated();
        unset($data['permissions']);
        $result = DB::transaction(function () use ($data, $userOrganisationRole): UserOrganisationRoleResource {
            $this->lockSuperAdminMutationSet();
            $userOrganisationRole->refresh();
            $this->guardLastSuperAdminAssignment($userOrganisationRole, $data);
            $userOrganisationRole->update($data);
            return new UserOrganisationRoleResource($userOrganisationRole->refresh());
        });
        return $request->expectsJson() ? $result : back()->with('success', 'Role assignment updated.');
    }
    public function destroy(Request $request, UserOrganisationRole $userOrganisationRole): Response|RedirectResponse
    {
        $this->authorize('delete', $userOrganisationRole);
        DB::transaction(function () use ($userOrganisationRole): void {
            $this->lockSuperAdminMutationSet();
            $userOrganisationRole->refresh();
            $this->guardLastSuperAdminAssignment($userOrganisationRole, ['is_active' => false]);
            $userOrganisationRole->delete();
        });
        return $request->expectsJson() ? response()->noContent() : back()->with('success', 'Role assignment removed.');
    }

}
