<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationUnit;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OrganisationUnitController extends Controller
{
    public function __construct(private HierarchyScopeService $scope) {}

    public function index(Request $request): View
    {
        $base = OrganisationUnit::query();
        if (! $this->isSuper($request)) {
            $base->whereIn('id', $this->scope->allowedUnitIds($request->user()));
        }

        $query = clone $base;
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.organisation.index', [
            'units' => $query->with('parent')->orderBy('type')->orderBy('name')->paginate(12)->withQueryString(),
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('is_active', true)->count(),
                'dioceses' => (clone $base)->where('type', 'diocese')->count(),
                'local_churches' => (clone $base)->where('type', 'local_church')->count(),
            ],
            'filters' => ['q' => $search, 'type' => $type, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:organisation_units,id',
            'type' => 'required|in:province,diocese,archdeaconry,parish,local_church,chaplaincy,institution',
            'name' => 'required|max:160',
            'code' => 'nullable|max:50',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:40',
        ]);
        if ($data['type'] !== 'province' && ! $this->isSuper($request) && ! $this->scope->canManage($request->user(), $data['parent_id'] ?? null)) {
            abort(403);
        }
        OrganisationUnit::create($data + ['is_active' => true]);
        return back()->with('success', 'Church structure added successfully.');
    }

    public function update(Request $request, OrganisationUnit $organisationUnit)
    {
        if (! $this->isSuper($request) && ! $this->scope->canManage($request->user(), $organisationUnit->id)) {
            abort(403);
        }
        $organisationUnit->update($request->validate([
            'name' => 'required|max:160',
            'code' => 'nullable|max:50',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:40',
            'is_active' => 'boolean',
        ]));
        return back()->with('success', 'Church structure updated successfully.');
    }

    public function destroy(Request $request, OrganisationUnit $organisationUnit)
    {
        if (! $this->isSuper($request) && ! $this->scope->canManage($request->user(), $organisationUnit->id)) {
            abort(403);
        }
        abort_if($organisationUnit->children()->exists(), 422, 'Move or remove child units first.');
        $organisationUnit->delete();
        return back()->with('success', 'Church structure removed.');
    }

    private function isSuper(Request $request): bool
    {
        return method_exists($request->user(), 'hasRole') && $request->user()->hasRole('super_admin');
    }
}
