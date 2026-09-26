<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()
            ->where('status', 'published')
            ->where('starts_at', '>=', now()->subDay())
            ->orderBy('starts_at');

        if ($request->filled('age_category')) {
            $age = (string) $request->string('age_category');
            $query->where(fn ($builder) => $builder
                ->whereNull('target_age_categories')
                ->orWhereJsonContains('target_age_categories', $age));
        }

        return response()->json($query->paginate(min(max($request->integer('per_page', 12), 1), 50)));
    }

    public function show(Event $event): JsonResponse
    {
        abort_unless($event->status === 'published', 404);

        return response()->json([
            'data' => $event->loadCount([
                'registrations' => fn ($query) => $query->whereIn('status', ['registered', 'confirmed', 'attended']),
            ]),
        ]);
    }

    public function register(Request $request, Event $event): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $registration = DB::transaction(function () use ($event, $userId): EventRegistration {
            $lockedEvent = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedEvent->status === 'published', 404);

            if (! $lockedEvent->registration_required) {
                throw ValidationException::withMessages([
                    'event' => 'Registration is not required for this event.',
                ]);
            }

            if ($lockedEvent->registration_deadline && now()->gt($lockedEvent->registration_deadline)) {
                throw ValidationException::withMessages([
                    'event' => 'Registration for this event has closed.',
                ]);
            }

            $existing = EventRegistration::query()
                ->where('event_id', $lockedEvent->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                return $existing;
            }

            if ($lockedEvent->capacity) {
                $used = EventRegistration::query()
                    ->where('event_id', $lockedEvent->id)
                    ->whereIn('status', ['registered', 'confirmed', 'attended'])
                    ->count();

                if ($used >= $lockedEvent->capacity) {
                    throw ValidationException::withMessages([
                        'event' => 'This event has reached capacity.',
                    ]);
                }
            }

            return EventRegistration::query()->create([
                'event_id' => $lockedEvent->id,
                'user_id' => $userId,
                'status' => 'registered',
                'payment_status' => (float) $lockedEvent->fee > 0 ? 'pending' : 'not_required',
            ]);
        });

        return response()->json([
            'message' => 'Event registration saved.',
            'data' => $registration,
        ], 201);
    }
}
