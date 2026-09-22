<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LifeGroup;
use App\Models\LifeGroupMember;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LifeGroupController extends Controller
{
    public function __construct(private HierarchyScopeService $scope) {}

    public function index(Request $request): View
    {
        $query = LifeGroup::with('organisationUnit')->withCount('members');
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'active');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('meeting_day', 'like', "%{$search}%")
                    ->orWhere('meeting_location', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.life_groups.index', [
            'groups' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'stats' => [
                'total' => LifeGroup::count(),
                'active' => LifeGroup::where('is_active', true)->count(),
                'inactive' => LifeGroup::where('is_active', false)->count(),
                'members' => LifeGroupMember::count(),
            ],
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (! empty($data['organisation_unit_id']) && ! $this->scope->canManage($request->user(), (int) $data['organisation_unit_id'])) {
            abort(403);
        }
        LifeGroup::create($data + ['is_active' => true]);
        return back()->with('success', 'Life Group created successfully.');
    }

    public function update(Request $request, LifeGroup $lifeGroup)
    {
        if ($lifeGroup->organisation_unit_id && ! $this->scope->canManage($request->user(), $lifeGroup->organisation_unit_id)) {
            abort(403);
        }
        $lifeGroup->update($this->validated($request));
        return back()->with('success', 'Life Group updated successfully.');
    }

    public function destroy(Request $request, LifeGroup $lifeGroup)
    {
        if ($lifeGroup->organisation_unit_id && ! $this->scope->canManage($request->user(), $lifeGroup->organisation_unit_id)) {
            abort(403);
        }
        $lifeGroup->update(['is_active' => false]);
        return back()->with('success', 'Life Group deactivated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'name' => 'required|max:180',
            'description' => 'nullable',
            'leader_user_id' => 'nullable|exists:users,id',
            'member_limit' => 'required|integer|min:2|max:12',
            'meeting_day' => 'nullable|max:20',
            'meeting_time' => 'nullable',
            'meeting_location' => 'nullable|max:180',
        ]);
    }
}
