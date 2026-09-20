<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController extends Controller
{
    public function __construct(private HierarchyScopeService $scope) {}

    public function index(Request $request): View
    {
        $query = Event::with('organisationUnit');
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

        $statusCounts = Event::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.events.index', [
            'events' => $query->orderByDesc('starts_at')->paginate(12)->withQueryString(),
            'stats' => [
                'total' => Event::count(),
                'published' => Event::where('status', 'published')->count(),
                'upcoming' => Event::where('starts_at', '>=', now())->whereNotIn('status', ['cancelled', 'completed'])->count(),
                'completed' => Event::where('status', 'completed')->count(),
            ],
            'statusCounts' => $statusCounts,
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (! empty($data['organisation_unit_id']) && ! $this->scope->canManage($request->user(), (int) $data['organisation_unit_id'])) {
            abort(403);
        }
        $data['created_by'] = $request->user()->id;
        Event::create($data);
        return back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, Event $event)
    {
        if ($event->organisation_unit_id && ! $this->scope->canManage($request->user(), $event->organisation_unit_id)) {
            abort(403);
        }
        $event->update($this->validated($request));
        return back()->with('success', 'Event updated successfully.');
    }

    public function destroy(Request $request, Event $event)
    {
        if ($event->organisation_unit_id && ! $this->scope->canManage($request->user(), $event->organisation_unit_id)) {
            abort(403);
        }
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'title' => 'required|max:200',
            'category' => 'nullable|max:100',
            'description' => 'nullable',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'venue' => 'nullable|max:200',
            'theme' => 'nullable|max:200',
            'speaker' => 'nullable|max:160',
            'organizer' => 'nullable|max:160',
            'target_age_categories' => 'nullable|array',
            'target_age_categories.*' => 'in:teen,youth,young_adult',
            'registration_required' => 'boolean',
            'fee' => 'nullable|numeric|min:0',
            'currency' => 'required|size:3',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);
    }
}
