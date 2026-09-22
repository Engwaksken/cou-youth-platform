<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private HierarchyScopeService $scope) {}

    public function index(Request $request)
    {
        $query = PlatformNotification::query()->with('organisationUnit')->latest();
        $search = trim((string) $request->query('q', ''));
        $channel = (string) $request->query('channel', '');
        $ageCategory = (string) $request->query('age_category', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }
        if ($channel !== '') $query->where('channel', $channel);
        if ($ageCategory !== '') $query->where('age_category', $ageCategory);

        return view('admin.notifications.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'stats' => [
                'total' => PlatformNotification::count(),
                'active' => PlatformNotification::where('is_active', true)->count(),
                'scheduled' => PlatformNotification::whereNotNull('scheduled_at')->where('scheduled_at', '>', now())->count(),
                'all_channel' => PlatformNotification::where('channel', 'all')->count(),
            ],
            'chart' => PlatformNotification::query()->selectRaw('channel, COUNT(*) as total')->groupBy('channel')->pluck('total', 'channel'),
            'filters' => ['q' => $search, 'channel' => $channel, 'age_category' => $ageCategory],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);
        PlatformNotification::create($data + ['created_by' => $request->user()->id, 'is_active' => true]);
        return back()->with('success', 'Notification queued successfully.');
    }

    public function update(Request $request, PlatformNotification $notification)
    {
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);
        $notification->update($data + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Notification updated successfully.');
    }

    public function destroy(Request $request, PlatformNotification $notification)
    {
        if ($notification->organisation_unit_id) {
            $this->authoriseUnit($request, $notification->organisation_unit_id);
        }
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|max:180',
            'body' => 'required|max:2000',
            'channel' => 'required|in:in_app,push,email,all',
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'age_category' => 'required|in:teen,youth,young_adult,all',
            'action_url' => 'nullable|max:255',
            'scheduled_at' => 'nullable|date',
        ]);
    }

    private function authoriseUnit(Request $request, mixed $unitId): void
    {
        if (! empty($unitId) && ! $this->scope->canManage($request->user(), (int) $unitId)) {
            abort(403);
        }
    }
}
