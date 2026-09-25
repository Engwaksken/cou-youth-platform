<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PrayerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class YouthCommunitySupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_life_groups_page_is_publicly_accessible(): void
    {
        $this->get('/life-groups')
            ->assertOk()
            ->assertSee('Life Groups');
    }

    public function test_prayer_page_requires_authentication(): void
    {
        $this->get('/prayer')
            ->assertRedirect('/login');
    }

    public function test_authenticated_youth_can_submit_and_view_own_prayer_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/prayer', [
                'subject' => 'Please pray for my studies',
                'message' => 'I would appreciate prayer for wisdom and focus this term.',
                'visibility' => 'private',
            ])
            ->assertRedirect('/prayer');

        $this->assertDatabaseHas('prayer_requests', [
            'user_id' => $user->id,
            'subject' => 'Please pray for my studies',
            'visibility' => 'private',
            'status' => 'submitted',
        ]);

        $request = PrayerRequest::query()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->get('/prayer')
            ->assertOk()
            ->assertSee($request->subject)
            ->assertSee('Submitted');
    }

    public function test_youth_cannot_submit_an_invalid_prayer_visibility(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/prayer')
            ->post('/prayer', [
                'subject' => 'Prayer request',
                'message' => 'Please pray for me.',
                'visibility' => 'everyone',
            ])
            ->assertRedirect('/prayer')
            ->assertSessionHasErrors('visibility');
    }
}
