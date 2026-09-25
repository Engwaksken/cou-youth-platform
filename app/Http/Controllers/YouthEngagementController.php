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
        $registrations = EventRegistration::query()
            ->with('event')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        $registeredEventIds = $registrations->getCollection()->pluck('event_id')->all();

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
        abort_unless($event->status === 'published', 404);

        if (! $event->registration_required) {
            return back()->withErrors(['event' => 'Registration is not required for this event.']);
        }

        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
            return back()->withErrors(['event' => 'Registration for this event has closed.']);
        }

        $existing = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $existing && $event->capacity) {
            $used = $event->registrations()
                ->whereIn('status', ['registered', 'confirmed', 'attended'])
                ->count();

            if ($used >= $event->capacity) {
                return back()->withErrors(['event' => 'This event has reached capacity.']);
            }
        }

        EventRegistration::firstOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
            ],
            [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'status' => 'registered',
                'payment_status' => (float) $event->fee > 0 ? 'pending' : 'not_required',
            ],
        );

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
