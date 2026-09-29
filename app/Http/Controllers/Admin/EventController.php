<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\Access\HierarchyScopeService;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController extends Controller
{
    public function __construct(
        private HierarchyScopeService $scope,
        private PlatformUpdateNotificationService $updates,
    ) {}

    public function index(Request $request): View
    {
        $base = Event::query();
        $this->scope->scopeQuery($base, $request->user());

        $query = (clone $base)
            ->with(['organisationUnit', 'registrations' => fn ($q) => $q->latest()])
            ->withCount([
                'registrations',
                'registrations as attended_count' => fn ($q) => $q->where('attendance_status', 'attended'),
                'registrations as absent_count' => fn ($q) => $q->where('attendance_status', 'absent'),
            ]);

        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['draft', 'published', 'cancelled', 'completed'], true)) {
            $query->where('status', $status);
        }

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.events.index', [
            'events' => $query->orderByDesc('starts_at')->paginate(12)->withQueryString(),
            'stats' => [
                'total' => (clone $base)->count(),
                'published' => (clone $base)->where('status', 'published')->count(),
                'upcoming' => (clone $base)->where('starts_at', '>=', now())->whereNotIn('status', ['cancelled', 'completed'])->count(),
                'completed' => (clone $base)->where('status', 'completed')->count(),
            ],
            'statusCounts' => $statusCounts,
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);

        $data['created_by'] = $request->user()->id;
        $event = Event::create($data);

        if ($event->status === 'published') {
            $this->updates->notifyYouth(
                'New event: '.$event->title,
                'A new youth event has been published. Open the event to see the date, venue and registration details.',
                route('public.events.show', $event),
                $event->organisation_unit_id,
                $event->target_age_categories,
                $request->user()->id,
            );
        }

        return back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authoriseUnit($request, $event->organisation_unit_id);
        $data = $this->validated($request);
        $this->authoriseUnit($request, $data['organisation_unit_id'] ?? null);

        $wasPublished = $event->status === 'published';
        $event->update($data);

        if ($event->status === 'published') {
            $this->updates->notifyYouth(
                $wasPublished ? 'Event updated: '.$event->title : 'New event: '.$event->title,
                $wasPublished
                    ? 'Event information has been updated. Open the event to review the latest details.'
                    : 'A new youth event has been published. Open the event to see the latest details.',
                route('public.events.show', $event),
                $event->organisation_unit_id,
                $event->target_age_categories,
                $request->user()->id,
            );
        }

        return back()->with('success', 'Event updated successfully.');
    }

    public function updateAttendance(Request $request, Event $event, EventRegistration $registration): RedirectResponse
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
        $this->authoriseUnit($request, $event->organisation_unit_id);

        $data = $request->validate([
            'attendance_status' => 'required|in:registered,attended,absent',
        ]);

        $registration->update([
            'attendance_status' => $data['attendance_status'],
            'attended_at' => $data['attendance_status'] === 'attended' ? now() : null,
        ]);

        return back()->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authoriseUnit($request, $event->organisation_unit_id);

        $title = $event->title;
        $unitId = $event->organisation_unit_id;
        $ages = $event->target_age_categories;
        $wasPublished = $event->status === 'published';
        $event->delete();

        if ($wasPublished) {
            $this->updates->notifyYouth(
                'Event removed: '.$title,
                'This event has been removed from the Church of Uganda Youth Platform.',
                null,
                $unitId,
                $ages,
                $request->user()->id,
            );
        }

        return back()->with('success', 'Event deleted.');
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
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'venue' => 'nullable|string|max:200',
            'theme' => 'nullable|string|max:200',
            'speaker' => 'nullable|string|max:160',
            'organizer' => 'nullable|string|max:160',
            'target_age_categories' => 'nullable|array',
            'target_age_categories.*' => 'in:teen,youth,young_adult',
            'registration_required' => 'boolean',
            'allow_external_registration' => 'boolean',
            'external_registration_url' => 'nullable|url:http,https|max:500',
            'fee' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);
    }
}
