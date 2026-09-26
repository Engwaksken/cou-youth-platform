<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GuardianConsent;
use App\Models\LifeGroup;
use App\Models\OrganisationUnit;
use App\Models\YouthProfile;
use App\Services\Access\HierarchyScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __construct(private readonly HierarchyScopeService $scope) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isSuper = $this->scope->isSuperAdmin($user);
        $allowedUnitIds = $this->scope->allowedUnitIds($user);

        $youthBase = YouthProfile::query();
        $eventBase = Event::query();
        $lifeGroupBase = LifeGroup::query();
        $contentBase = Content::query();
        $unitBase = OrganisationUnit::query();

        if (! $isSuper) {
            $youthBase->whereIn('organisation_unit_id', $allowedUnitIds);
            $eventBase->whereIn('organisation_unit_id', $allowedUnitIds);
            $lifeGroupBase->whereIn('organisation_unit_id', $allowedUnitIds);
            $contentBase->whereIn('organisation_unit_id', $allowedUnitIds);
            $unitBase->whereIn('id', $allowedUnitIds);
        }

        $donationBase = Donation::query();
        if (! $isSuper) {
            $donationBase->whereIn('user_id', YouthProfile::query()
                ->whereIn('organisation_unit_id', $allowedUnitIds)
                ->select('user_id'));
        }

        $consentBase = GuardianConsent::query();
        if (! $isSuper) {
            $consentBase->whereIn('user_id', YouthProfile::query()
                ->whereIn('organisation_unit_id', $allowedUnitIds)
                ->select('user_id'));
        }

        $stats = [
            'youth' => (clone $youthBase)->count(),
            'units' => (clone $unitBase)->where('is_active', true)->count(),
            'events' => (clone $eventBase)->count(),
            'life_groups' => (clone $lifeGroupBase)->where('is_active', true)->count(),
            'pending_consents' => (clone $consentBase)->where('status', 'pending')->count(),
            'published_content' => (clone $contentBase)->where('status', 'published')->count(),
            'donations' => (float) (clone $donationBase)
                ->whereIn('status', ['successful', 'paid', 'completed'])
                ->sum('amount'),
        ];

        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $from = $months->first()?->copy()->startOfMonth() ?? now()->startOfMonth();

        $youthByMonth = (clone $youthBase)
            ->where('created_at', '>=', $from)
            ->get(['created_at'])
            ->groupBy(fn (YouthProfile $profile) => $profile->created_at?->format('Y-m'))
            ->map->count();

        $donationsByMonth = (clone $donationBase)
            ->whereIn('status', ['successful', 'paid', 'completed'])
            ->where('created_at', '>=', $from)
            ->get(['amount', 'created_at'])
            ->groupBy(fn (Donation $donation) => $donation->created_at?->format('Y-m'))
            ->map(fn ($items) => (float) $items->sum('amount'));

        $trend = $months->map(fn (Carbon $month) => [
            'label' => $month->format('M Y'),
            'youth' => (int) ($youthByMonth[$month->format('Y-m')] ?? 0),
            'donations' => (float) ($donationsByMonth[$month->format('Y-m')] ?? 0),
        ])->values();

        $eventStatus = (clone $eventBase)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', compact('stats', 'trend', 'eventStatus'));
    }
}
