<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
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
        $users = User::query()->orderBy('id')->paginate(20);
        return $request->expectsJson() ? UserResource::collection($users) : view('admin.users.index', compact('users'));
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
        DB::transaction(function () use ($user): void {
            $this->lockSuperAdminMutationSet();
            $user->refresh();
            $this->guardLastSuperAdminUserDeletion($user);
            $user->delete();
        });
        return $request->expectsJson() ? response()->noContent() : back()->with('success', 'User removed.');
    }
}
