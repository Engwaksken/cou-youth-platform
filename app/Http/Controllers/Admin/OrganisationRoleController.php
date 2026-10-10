<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganisationRoleRequest;
use App\Http\Requests\Admin\UpdateOrganisationRoleRequest;
use App\Http\Resources\Admin\OrganisationRoleResource;
use App\Models\OrganisationRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Models\OrganisationUnit;
use Illuminate\Http\RedirectResponse;

final class OrganisationRoleController extends Controller
{
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
        $role = OrganisationRole::create($request->validated());
        if ($request->expectsJson()) return (new OrganisationRoleResource($role))->response()->setStatusCode(201);
        return back()->with('success', 'Organisation role created.');
    }

    public function update(UpdateOrganisationRoleRequest $request, OrganisationRole $organisationRole): Response|RedirectResponse|OrganisationRoleResource
    {
        $this->authorize('update', $organisationRole);
        $organisationRole->update($request->validated());
        if ($request->expectsJson()) return new OrganisationRoleResource($organisationRole->refresh());
        return back()->with('success', 'Organisation role updated.');
    }

    public function destroy(Request $request, OrganisationRole $organisationRole): Response|RedirectResponse
    {
        $this->authorize('delete', $organisationRole);
        $organisationRole->delete();
        return $request->expectsJson() ? response()->noContent() : back()->with('success', 'Organisation role removed.');
    }
}
