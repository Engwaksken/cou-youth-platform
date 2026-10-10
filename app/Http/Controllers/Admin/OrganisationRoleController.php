<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganisationRoleRequest;
use App\Http\Requests\Admin\UpdateOrganisationRoleRequest;
use App\Http\Resources\Admin\OrganisationRoleResource;
use App\Models\OrganisationRole;
use App\Models\UserOrganisationRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Models\OrganisationUnit;
use Illuminate\Http\RedirectResponse;

final class OrganisationRoleController extends Controller
{
    use GuardsSuperAdminMutations;
    public function index(Request $request): View|AnonymousResourceCollection
    {
        $this->authorize('viewAny', OrganisationRole::class);
        $roles = OrganisationRole::query()->with('organisationUnit')->orderBy('organisation_unit_id')->orderBy('name')->paginate(20);
        if ($request->expectsJson()) return OrganisationRoleResource::collection($roles);
        return view('admin.organisation-roles.index', ['roles' => $roles, 'organisationUnits' => OrganisationUnit::query()->orderBy('name')->get()]);
    }

    public function store(StoreOrganisationRoleRequest $request): JsonResponse|RedirectResponse|OrganisationRoleResource
    {
        $this->authorize('create', OrganisationRole::class);
        try {
        $role = $this->withinSuperAdminMutationLock(function () use ($request): OrganisationRole {
            $role = new OrganisationRole($request->validated());
            $role->save();

            return $role;
        });
        } catch (QueryException $exception) {
            $this->abortRoleConflict($exception);
            throw $exception;
        }
        if ($request->expectsJson()) return (new OrganisationRoleResource($role))->response()->setStatusCode(201);
        return back()->with('success', 'Organisation role created.');
    }

    public function update(UpdateOrganisationRoleRequest $request, OrganisationRole $organisationRole): Response|RedirectResponse|OrganisationRoleResource
    {
        $this->authorize('update', $organisationRole);
        try {
        $this->withinSuperAdminMutationLock(function () use ($request, $organisationRole): void {
            OrganisationRole::withRoleLock((int) $organisationRole->id, function (OrganisationRole $role) use ($request): void {
            $previousName = $role->name;
            $previousUnitId = $role->organisation_unit_id;
            $assignments = $role->assignments()->lockForUpdate()->get();
            $data = $request->validated();
            $role->fill($data);
            $this->ensureAssignmentsCanFollowRole($assignments, $role);
            $role->saveQuietly();
            $assignments->each(function (UserOrganisationRole $assignment) use ($role): void {
                // The assignment saving hook reads its catalog role relation. It
                // must see the updated model rather than a stale cached relation.
                $assignment->setRelation('organisationRole', $role);
                $assignment->role = $role->name;
                // A global catalog role does not impose a unit scope. Keep each
                // linked assignment in its existing scope when the role is global.
                $assignment->permissions = $role->permissions;
                $assignment->save();
            });
            // Legacy rows intentionally stay unlinked and keep their role/scope,
            // but permissions still track an edited catalog entry with a matching
            // legacy name in its former scope.
            UserOrganisationRole::query()
                ->whereNull('organisation_role_id')
                ->where('role', $previousName)
                ->when($previousUnitId !== null, fn ($query) => $query->where('organisation_unit_id', $previousUnitId))
                ->get()
                ->each(function (UserOrganisationRole $assignment) use ($role): void {
                    $assignment->permissions = $role->permissions;
                    $assignment->save();
                });
            });
        });
        } catch (QueryException $exception) {
            $this->abortRoleConflict($exception);
            throw $exception;
        }
        if ($request->expectsJson()) return new OrganisationRoleResource($organisationRole->refresh());
        return back()->with('success', 'Organisation role updated.');
    }

    /** Reject a scope/name change that would collapse distinct assignments onto
     * the unique user/scope/role key. The surrounding transaction keeps this
     * check and all resulting assignment writes atomic. */
    private function ensureAssignmentsCanFollowRole($assignments, OrganisationRole $role): void
    {
        $targetName = $role->name;
        $assignmentIds = $assignments->modelKeys();
        $seenUsers = [];

        foreach ($assignments as $assignment) {
            // A global catalog role leaves each linked assignment in its
            // existing organisation scope; it does not merge scopes.
            // Catalog scope does not own the assignment's organisation scope.
            $targetUnitId = $assignment->organisation_unit_id;
            $key = $assignment->user_id.':'.($targetUnitId ?? 'null').':'.$targetName;
            if (isset($seenUsers[$key])) {
                abort(409, 'This role change would create duplicate user assignments in the target scope.');
            }
            $seenUsers[$key] = true;

            $collisionQuery = UserOrganisationRole::query()
                ->where('user_id', $assignment->user_id)
                ->where('role', $targetName)
                ->whereNotIn('id', $assignmentIds);
            $targetUnitId === null
                ? $collisionQuery->whereNull('organisation_unit_id')
                : $collisionQuery->where('organisation_unit_id', $targetUnitId);
            $collision = $collisionQuery->exists();
            abort_if($collision, 409, 'This role change would create duplicate user assignments in the target scope.');
        }
    }

    private function abortRoleConflict(QueryException $exception): void
    {
        $message = strtolower($exception->getMessage());
        if (str_contains($message, 'organisation role name conflicts across scopes')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'duplicate entry')
            || str_contains($message, 'duplicate key')) {
            abort(409, 'The role name or assignment conflicts with an existing record.');
        }
    }

    public function destroy(Request $request, OrganisationRole $organisationRole): Response|RedirectResponse
    {
        $this->authorize('delete', $organisationRole);
        $this->withinSuperAdminMutationLock(function () use ($organisationRole): void {
            OrganisationRole::withRoleLock((int) $organisationRole->id, function (OrganisationRole $role) use ($organisationRole): void {
            $assignedDirectly = $role->assignments()->exists();
            $assignedByLegacyReference = UserOrganisationRole::query()
                ->whereNull('organisation_role_id')
                ->where('role', $role->name)
                ->when($role->organisation_unit_id !== null, fn ($query) => $query->where('organisation_unit_id', $role->organisation_unit_id))
                ->exists();
            abort_if($assignedDirectly || $assignedByLegacyReference, 422, 'This role is assigned and cannot be deleted.');
            $role->delete();
            });
        });
        return $request->expectsJson() ? response()->noContent() : back()->with('success', 'Organisation role removed.');
    }
}
