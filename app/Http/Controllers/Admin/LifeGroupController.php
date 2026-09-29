<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LifeGroup;
use App\Models\LifeGroupMember;
use App\Services\Access\HierarchyScopeService;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LifeGroupController extends Controller
{
    public function __construct(
        private HierarchyScopeService $scope,
        private PlatformUpdateNotificationService $updates,
    ) {}

    public function index(Request $request): View
    {
        $base = LifeGroup::query();
        $this->scope->scopeQuery($base, $request->user());

        $query = (clone $base)->with('organisationUnit')->withCount('members');
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

        $groupIds = (clone $base)->select('id');

        return view('admin.life_groups.index', [
            'groups' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('is_active', true)->count(),
                'inactive' => (clone $base)->where('is_active', false)->count(),
                'members' => LifeGroupMember::query()->whereIn('life_group_id', $groupIds)->count(),
            ],
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);

        $group = LifeGroup::create($data + ['is_active' => true]);

        $this->updates->notifyYouth(
            'New Life Group: '.$group->name,
            'A new Life Group is available. Open the app to view meeting details and join where eligible.',
            route('home'),
            $group->organisation_unit_id,
            'all',
            $request->user()?->id,
        );

        return back()->with('success', 'Life Group created successfully.');
    }

    public function update(Request $request, LifeGroup $lifeGroup)
    {
        $this->authoriseUnit($request, $lifeGroup->organisation_unit_id);
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);

        $lifeGroup->update($data);

        if ($lifeGroup->is_active) {
            $this->updates->notifyYouth(
                'Life Group updated: '.$lifeGroup->name,
                'Life Group information has changed. Open the app to review the latest meeting details.',
                route('home'),
                $lifeGroup->organisation_unit_id,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Life Group updated successfully.');
    }

    public function destroy(Request $request, LifeGroup $lifeGroup)
    {
        $this->authoriseUnit($request, $lifeGroup->organisation_unit_id);
        $lifeGroup->update(['is_active' => false]);

        $this->updates->notifyYouth(
            'Life Group unavailable: '.$lifeGroup->name,
            'This Life Group has been deactivated. Open the app to find other available groups.',
            route('home'),
            $lifeGroup->organisation_unit_id,
            'all',
            $request->user()?->id,
        );

        return back()->with('success', 'Life Group deactivated.');
    }

    private function authoriseUnit(Request $request, mixed $unitId): void
    {
        $normalised = $unitId === null || $unitId === '' ? null : (int) $unitId;
        if (! $this->scope->canManage($request->user(), $normalised)) {
            abort(403);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'name' => 'required|string|max:180',
            'description' => 'nullable|string',
            'leader_user_id' => 'nullable|exists:users,id',
            'member_limit' => 'required|integer|min:2|max:12',
            'meeting_day' => 'nullable|string|max:20',
            'meeting_time' => 'nullable',
            'meeting_location' => 'nullable|string|max:180',
        ]);
    }
}
