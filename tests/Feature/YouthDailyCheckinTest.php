<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\YouthCheckin;
use App\Services\Engagement\DailyEngagementService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class YouthDailyCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_check_in_requires_authentication(): void
    {
        $this->post(route('youth.check-in'))->assertRedirect('/login');
        $this->assertDatabaseCount('youth_checkins', 0);
    }

    public function test_checking_in_twice_on_the_same_day_records_one_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('youth.check-in'))
            ->assertRedirect(route('youth.dashboard'))
            ->assertSessionHas('success', 'Checked in. Day one, let’s go!');

        $this->actingAs($user)->post(route('youth.check-in'))
            ->assertSessionHas('success', "You're already checked in for today. See you tomorrow!");

        $this->assertSame(1, YouthCheckin::query()->where('user_id', $user->id)->count());

        $this->actingAs($user)->get(route('youth.dashboard'))
            ->assertOk()
            ->assertSee('Checked in today');
    }

    public function test_days_follow_kampala_time_not_utc(): void
    {
        $user = User::factory()->create();
        // 22:30 UTC on 1 March is already 01:30 on 2 March in Kampala (UTC+3).
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-01 22:30:00', 'UTC'));

        $this->actingAs($user)->post(route('youth.check-in'));

        $this->assertSame('2026-03-02', YouthCheckin::query()->sole()->checked_in_on->toDateString());
    }

    public function test_streak_counts_consecutive_days_and_survives_until_the_day_ends(): void
    {
        $user = User::factory()->create();
        $service = app(DailyEngagementService::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 09:00:00', 'Africa/Kampala'));

        // A 4-day run that ended yesterday, plus an older 5-day run.
        foreach (['2026-03-06', '2026-03-07', '2026-03-08', '2026-03-09', '2026-02-01', '2026-02-02', '2026-02-03', '2026-02-04', '2026-02-05'] as $day) {
            YouthCheckin::query()->create(['user_id' => $user->id, 'checked_in_on' => $day]);
        }

        $streak = $service->streak($user);
        $this->assertSame(4, $streak['current'], 'Yesterday’s run is still alive until today ends.');
        $this->assertFalse($streak['checked_in_today']);
        $this->assertSame(5, $streak['best']);
        $this->assertSame(7, $streak['next_milestone']);
        $this->assertCount(7, $streak['week']);

        $service->checkIn($user);
        $this->assertSame(5, $service->streak($user)['current']);

        // Skipping a whole day resets the current streak but keeps the history.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-12 09:00:00', 'Africa/Kampala'));
        $streak = $service->streak($user);
        $this->assertSame(0, $streak['current']);
        $this->assertSame(10, $streak['total']);
    }

    public function test_verse_of_the_day_rotates_through_the_configured_list(): void
    {
        config(['cou_youth.daily_verses' => [
            ['ref' => 'A 1:1', 'text' => 'First'],
            ['ref' => 'B 1:1', 'text' => 'Second'],
        ]]);
        $service = app(DailyEngagementService::class);

        $monday = CarbonImmutable::parse('2026-01-05');
        $this->assertNotSame(
            $service->verseOfTheDay($monday)['ref'],
            $service->verseOfTheDay($monday->addDay())['ref'],
        );

        config(['cou_youth.daily_verses' => []]);
        $this->assertNull($service->verseOfTheDay($monday));
    }
}
