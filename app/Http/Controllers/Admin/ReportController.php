<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Donation;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\LifeGroup;
use App\Models\OrganisationUnit;
use App\Models\YouthProfile;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(private readonly HierarchyScopeService $scope) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isSuper = $this->scope->isSuperAdmin($user);
        $allowedUnitIds = $this->scope->allowedUnitIds($user);
        $unit = $request->integer('organisation_unit_id') ?: null;

        if ($unit !== null && ! $this->scope->canManage($user, $unit)) {
            abort(403);
        }

        $effectiveUnits = $unit !== null ? collect([$unit]) : $allowedUnitIds;

        $youth = YouthProfile::query();
        $events = Event::query();
        $groups = LifeGroup::query();
        $content = Content::query();

        if (! $isSuper || $unit !== null) {
            $youth->whereIn('organisation_unit_id', $effectiveUnits);
            $events->whereIn('organisation_unit_id', $effectiveUnits);
            $groups->whereIn('organisation_unit_id', $effectiveUnits);
            $content->whereIn('organisation_unit_id', $effectiveUnits);
        }

        $eventIds = (clone $events)->select('id');
        $youthUserIds = (clone $youth)->select('user_id');

        $donations = Donation::query();
        if (! $isSuper || $unit !== null) {
            $donations->whereIn('user_id', $youthUserIds);
        }

        $units = OrganisationUnit::query()
            ->when(! $isSuper, fn ($builder) => $builder->whereIn('id', $allowedUnitIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.index', [
            'units' => $units,
            'summary' => [
                'youth' => (clone $youth)->count(),
                'teens' => (clone $youth)->where('age_category', 'teen')->count(),
                'events' => (clone $events)->count(),
                'event_registrations' => EventRegistration::query()->whereIn('event_id', $eventIds)->count(),
                'life_groups' => (clone $groups)->where('is_active', true)->count(),
                'published_content' => (clone $content)->where('status', 'published')->count(),
                'successful_donations' => (float) $donations
                    ->whereIn('status', ['successful', 'paid', 'completed'])
                    ->sum('amount'),
            ],
        ]);
    }
}
