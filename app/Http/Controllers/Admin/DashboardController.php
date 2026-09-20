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
use Carbon\Carbon;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'youth' => YouthProfile::count(),
            'units' => OrganisationUnit::where('is_active', true)->count(),
            'events' => Event::count(),
            'life_groups' => LifeGroup::where('is_active', true)->count(),
            'pending_consents' => GuardianConsent::where('status', 'pending')->count(),
            'published_content' => Content::where('status', 'published')->count(),
            'donations' => (float) Donation::where('status', 'successful')->sum('amount'),
        ];

        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $from = $months->first()?->copy()->startOfMonth() ?? now()->startOfMonth();

        $youthByMonth = YouthProfile::query()
            ->where('created_at', '>=', $from)
            ->get(['created_at'])
            ->groupBy(fn (YouthProfile $profile) => $profile->created_at?->format('Y-m'))
            ->map->count();

        $donationsByMonth = Donation::query()
            ->where('status', 'successful')
            ->where('created_at', '>=', $from)
            ->get(['amount', 'created_at'])
            ->groupBy(fn (Donation $donation) => $donation->created_at?->format('Y-m'))
            ->map(fn ($items) => (float) $items->sum('amount'));

        $trend = $months->map(fn (Carbon $month) => [
            'label' => $month->format('M Y'),
            'youth' => (int) ($youthByMonth[$month->format('Y-m')] ?? 0),
            'donations' => (float) ($donationsByMonth[$month->format('Y-m')] ?? 0),
        ])->values();

        $eventStatus = Event::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', compact('stats', 'trend', 'eventStatus'));
    }
}
