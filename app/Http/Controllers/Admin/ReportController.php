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

        // Dioceses this admin may report on.
        $dioceses = OrganisationUnit::query()
            ->where('type', 'diocese')
            ->when(! $isSuper, fn ($builder) => $builder->whereIn('id', $allowedUnitIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $dioceseId = $request->integer('diocese_id') ?: null;
        if ($dioceseId !== null && ! $dioceses->contains('id', $dioceseId)) {
            abort(403);
        }

        if ($dioceseId !== null) {
            $youth->where('diocese_id', $dioceseId);
        }

        $eventIds = (clone $events)->select('id');
        $youthUserIds = (clone $youth)->select('user_id');

        $donations = Donation::query();
        if ($dioceseId !== null) {
            $donations->where('diocese_id', $dioceseId);
        }
        if (! $isSuper || $unit !== null) {
            $donations->whereIn('user_id', $youthUserIds);
        }

        $units = OrganisationUnit::query()
            ->when(! $isSuper, fn ($builder) => $builder->whereIn('id', $allowedUnitIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $successful = ['successful', 'paid', 'completed'];
        $youthByDiocese = (clone $youth)
            ->whereNotNull('diocese_id')
            ->selectRaw('diocese_id, COUNT(*) as total')
            ->groupBy('diocese_id')
            ->pluck('total', 'diocese_id');
        $donationsByDiocese = (clone $donations)
            ->whereIn('status', $successful)
            ->whereNotNull('diocese_id')
            ->selectRaw('diocese_id, COUNT(*) as donation_count, SUM(amount) as amount')
            ->groupBy('diocese_id')
            ->get()
            ->keyBy('diocese_id');

        $dioceseBreakdown = $dioceses
            ->when($dioceseId !== null, fn ($list) => $list->where('id', $dioceseId))
            ->map(fn ($diocese) => (object) [
                'id' => $diocese->id,
                'name' => $diocese->name,
                'youth' => (int) ($youthByDiocese[$diocese->id] ?? 0),
                'donation_count' => (int) ($donationsByDiocese[$diocese->id]->donation_count ?? 0),
                'donations' => (float) ($donationsByDiocese[$diocese->id]->amount ?? 0),
            ])
            ->filter(fn ($row) => $dioceseId !== null || $row->youth > 0 || $row->donation_count > 0)
            ->sortByDesc('youth')
            ->values();

        return view('admin.reports.index', [
            'units' => $units,
            'dioceses' => $dioceses,
            'dioceseId' => $dioceseId,
            'dioceseBreakdown' => $dioceseBreakdown,
            'youthWithoutDiocese' => (clone $youth)->whereNull('diocese_id')->count(),
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
