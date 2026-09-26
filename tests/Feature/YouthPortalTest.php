<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LifeGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class YouthPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_youth_can_open_dashboard_and_profile(): void
    {
        $user = User::factory()->create(['name' => 'Youth Member']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Youth Member')
            ->assertSee('Your profile');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Youth profile');
    }

    public function test_authenticated_youth_can_create_and_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'date_of_birth' => '2003-04-15',
                'age_category' => 'youth',
                'school_institution' => 'Uganda Christian University',
                'interests' => 'technology, music',
                'talents' => 'leadership, singing',
                'skills' => 'coding, design',
                'ministry_interests' => 'media, worship',
                'profile_public' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('youth_profiles', [
            'user_id' => $user->id,
            'age_category' => 'youth',
            'school_institution' => 'Uganda Christian University',
            'profile_public' => true,
        ]);
    }

    public function test_youth_can_join_and_leave_an_active_life_group(): void
    {
        $user = User::factory()->create();
        $group = LifeGroup::create([
            'name' => 'Young Disciples Fellowship',
            'description' => 'Weekly youth fellowship.',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/my-life-groups/'.$group->id.'/join')
            ->assertRedirect();

        $this->assertDatabaseHas('life_group_members', [
            'life_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->delete('/my-life-groups/'.$group->id.'/leave')
            ->assertRedirect();

        $this->assertDatabaseHas('life_group_members', [
            'life_group_id' => $group->id,
            'user_id' => $user->id,
            'status' => 'left',
        ]);
    }

    public function test_profile_ignores_submitted_age_category_and_derives_it_from_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'date_of_birth' => '2004-01-01',
                'age_category' => 'adult',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('youth_profiles', [
            'user_id' => $user->id,
            'date_of_birth' => '2004-01-01',
            'age_category' => 'youth',
        ]);
    }
}
