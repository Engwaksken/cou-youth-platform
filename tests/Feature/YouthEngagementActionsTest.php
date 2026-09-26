<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class YouthEngagementActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_engagement_pages(): void
    {
        $this->get('/my-events')->assertRedirect('/login');
        $this->get('/my-certificates')->assertRedirect('/login');
        $this->get('/notification-preferences')->assertRedirect('/login');
    }

    public function test_youth_can_enrol_and_complete_course_lessons(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'title' => 'Foundations of Faith',
            'age_category' => 'all',
            'is_published' => true,
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Introduction',
            'position' => 1,
            'is_published' => true,
        ]);

        $this->actingAs($user)
            ->post('/courses/'.$course->id.'/enrol')
            ->assertRedirect();

        $this->assertDatabaseHas('course_enrolments', [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'progress_percent' => 0,
        ]);

        $this->actingAs($user)
            ->post('/lessons/'.$lesson->id.'/complete')
            ->assertRedirect();

        $this->assertDatabaseHas('course_enrolments', [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'progress_percent' => 100,
        ]);

        $this->assertDatabaseHas('course_certificates', [
            'course_id' => $course->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_youth_can_register_for_a_published_event(): void
    {
        $user = User::factory()->create(['name' => 'Youth Member']);
        $event = Event::create([
            'title' => 'Youth Convention',
            'starts_at' => now()->addWeek(),
            'status' => 'published',
            'registration_required' => true,
            'fee' => 0,
            'currency' => 'UGX',
            'capacity' => 100,
        ]);

        $this->actingAs($user)
            ->post('/my-events/'.$event->id.'/register')
            ->assertRedirect();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'payment_status' => 'not_required',
        ]);
    }

    public function test_event_capacity_is_enforced(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $event = Event::create([
            'title' => 'Small Youth Gathering',
            'starts_at' => now()->addWeek(),
            'status' => 'published',
            'registration_required' => true,
            'fee' => 0,
            'currency' => 'UGX',
            'capacity' => 1,
        ]);

        $event->registrations()->create([
            'user_id' => $other->id,
            'status' => 'registered',
            'payment_status' => 'not_required',
        ]);

        $this->actingAs($user)
            ->from('/my-events')
            ->post('/my-events/'.$event->id.'/register')
            ->assertRedirect('/my-events')
            ->assertSessionHasErrors('event');

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_youth_can_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/notification-preferences', [
                'in_app' => '1',
                'events' => '1',
                'discipleship' => '1',
            ])
            ->assertRedirect();

        $preferences = NotificationPreference::where('user_id', $user->id)->firstOrFail();

        $this->assertTrue($preferences->in_app);
        $this->assertTrue($preferences->events);
        $this->assertTrue($preferences->discipleship);
        $this->assertFalse($preferences->push);
        $this->assertFalse($preferences->email);
        $this->assertFalse($preferences->donations);
    }
}
