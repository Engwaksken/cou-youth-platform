<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrayerRequest;
use App\Models\YouthProfile;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;

final class PrayerRequestController extends Controller
{
    public function __construct(private readonly HierarchyScopeService $scope) {}

    public function index(Request $request)
    {
        $base = $this->scopedQuery($request);
        $query = (clone $base)->latest();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('pastoral_notes', 'like', "%{$search}%");
            });
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, ['submitted', 'under_review', 'referred', 'resolved', 'closed'], true)) {
            $query->where('status', $status);
        }

        $safeguarding = $request->boolean('safeguarding');
        if ($safeguarding) {
            $query->where('requires_safeguarding_review', true);
        }

        $stats = [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->whereIn('status', ['submitted', 'under_review', 'referred'])->count(),
            'resolved' => (clone $base)->whereIn('status', ['resolved', 'closed'])->count(),
            'safeguarding' => (clone $base)->where('requires_safeguarding_review', true)->count(),
        ];

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.prayer.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'safeguarding' => $safeguarding,
            ],
        ]);
    }

    public function update(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorisePrayerRequest($request, $prayerRequest);

        $data = $request->validate([
            'status' => 'required|in:submitted,under_review,referred,resolved,closed',
            'pastoral_notes' => 'nullable|string|max:5000',
        ]);

        $prayerRequest->update($data);

        return back()->with('success', 'Prayer/pastoral case updated.');
    }

    private function scopedQuery(Request $request)
    {
        $query = PrayerRequest::query();
        if (! $this->scope->isSuperAdmin($request->user())) {
            $query->whereIn('user_id', YouthProfile::query()
                ->whereIn('organisation_unit_id', $this->scope->allowedUnitIds($request->user()))
                ->select('user_id'));
        }

        return $query;
    }

    private function authorisePrayerRequest(Request $request, PrayerRequest $prayerRequest): void
    {
        if ($this->scope->isSuperAdmin($request->user())) {
            return;
        }

        $allowed = YouthProfile::query()
            ->where('user_id', $prayerRequest->user_id)
            ->whereIn('organisation_unit_id', $this->scope->allowedUnitIds($request->user()))
            ->exists();

        abort_unless($allowed, 403);
    }
}
