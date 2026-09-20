<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrayerRequest;
use Illuminate\Http\Request;

class PrayerRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PrayerRequest::query()->latest();

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
            'total' => PrayerRequest::count(),
            'open' => PrayerRequest::whereIn('status', ['submitted', 'under_review', 'referred'])->count(),
            'resolved' => PrayerRequest::whereIn('status', ['resolved', 'closed'])->count(),
            'safeguarding' => PrayerRequest::where('requires_safeguarding_review', true)->count(),
        ];

        $statusCounts = PrayerRequest::query()
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
        $data = $request->validate([
            'status' => 'required|in:submitted,under_review,referred,resolved,closed',
            'pastoral_notes' => 'nullable|string|max:5000',
        ]);

        $prayerRequest->update($data);

        return back()->with('success', 'Prayer/pastoral case updated.');
    }
}
