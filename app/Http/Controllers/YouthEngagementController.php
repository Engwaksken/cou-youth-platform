<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseEnrolment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Lesson;
use App\Models\NotificationPreference;
use App\Services\Certificates\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class YouthEngagementController extends Controller
{
    public function enrolCourse(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        CourseEnrolment::firstOrCreate([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'You are now enrolled in '.$course->title.'.');
    }

    public function completeLesson(Request $request, Lesson $lesson, CertificateService $certificates): RedirectResponse
    {
        abort_unless($lesson->is_published, 404);

        $course = $lesson->course;
        abort_unless($course && $course->is_published, 404);

        CourseEnrolment::firstOrCreate([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
        ]);

        DB::table('lesson_progress')->updateOrInsert(
            [
                'lesson_id' => $lesson->id,
                'user_id' => $request->user()->id,
            ],
            [
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $publishedLessonIds = $course->lessons()
            ->where('is_published', true)
            ->pluck('id');

        $total = max(1, $publishedLessonIds->count());
        $completed = DB::table('lesson_progress')
            ->where('user_id', $request->user()->id)
            ->whereIn('lesson_id', $publishedLessonIds)
            ->whereNotNull('completed_at')
            ->count();

        $progress = (int) round(($completed / $total) * 100);

        CourseEnrolment::query()
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'progress_percent' => $progress,
                'completed_at' => $progress === 100 ? now() : null,
                'updated_at' => now(),
            ]);

        if ($progress === 100) {
            $certificates->issue($course, $request->user());
        }

        return back()->with('success', $progress === 100
            ? 'Course completed. Your certificate is ready.'
            : 'Lesson completed. Course progress is now '.$progress.'%.');
    }

    public function events(Request $request): View
    {
        $userId = $request->user()->id;

        $registrations = EventRegistration::query()
            ->with('event')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(12);

        // Exclude every event already registered by this user, not only the
        // registrations visible on the current paginator page.
        $registeredEventIds = EventRegistration::query()
            ->where('user_id', $userId)
            ->pluck('event_id');

        $upcomingEvents = Event::query()
            ->where('status', 'published')
            ->where('starts_at', '>=', now()->subHours(3))
            ->whereNotIn('id', $registeredEventIds)
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        return view('youth.events', compact('registrations', 'upcomingEvents'));
    }

    public function registerEvent(Request $request, Event $event): RedirectResponse
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($event, $userId): void {
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
                return;
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

            EventRegistration::query()->create([
                'event_id' => $lockedEvent->id,
                'user_id' => $userId,
                'status' => 'registered',
                'payment_status' => (float) $lockedEvent->fee > 0 ? 'pending' : 'not_required',
            ]);
        });

        return back()->with('success', 'Your event registration has been saved.');
    }

    public function certificates(Request $request): View
    {
        $certificates = CourseCertificate::query()
            ->with('course')
            ->where('user_id', $request->user()->id)
            ->latest('issued_at')
            ->paginate(12);

        return view('youth.certificates', compact('certificates'));
    }

    public function preferences(Request $request): View
    {
        $preferences = NotificationPreference::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return view('youth.notification-preferences', compact('preferences'));
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $fields = [
            'in_app',
            'push',
            'email',
            'events',
            'discipleship',
            'opportunities',
            'donations',
            'life_groups',
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $request->boolean($field);
        }

        NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data,
        );

        return back()->with('success', 'Notification preferences updated.');
    }
}
