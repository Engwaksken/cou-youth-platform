<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

final class UserController extends Controller
{
    use GuardsSuperAdminMutations;

    public function index(Request $request): View|AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);
        $dioceseId = $request->integer('diocese_id');
        $users = User::query()
            ->with('youthProfile.diocese')
            ->when($dioceseId > 0, fn ($query) => $query->whereHas('youthProfile', fn ($profile) => $profile->where('diocese_id', $dioceseId)))
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
        $dioceses = ChurchOfUgandaDioceses::options();

        return $request->expectsJson() ? UserResource::collection($users) : view('admin.users.index', compact('users', 'dioceses', 'dioceseId'));
    }
    public function store(StoreUserRequest $request): UserResource|JsonResponse|RedirectResponse
    {
        $user = User::create($request->validated());
        return $request->expectsJson() ? (new UserResource($user))->response()->setStatusCode(201) : back()->with('success', 'User created.');
    }
    public function show(User $user): UserResource { $this->authorize('view', $user); return new UserResource($user); }
    public function update(UpdateUserRequest $request, User $user): UserResource|RedirectResponse
    {
        $this->authorize('update', $user);
        $user->update($request->validated());

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
        return $request->expectsJson() ? response()->noContent() : back()->with('success', 'User removed.');
    }
}
