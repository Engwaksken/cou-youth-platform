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
            ->assertSee('Youth Member')
            ->assertSee('<span class="scribble">Youth</span>', false)
            ->assertSee('Your profile')
            ->assertSee('day streak');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSeeText('All about you');
    }

    public function test_authenticated_youth_can_create_and_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'diocese_id' => $this->dioceseId(),
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
                'diocese_id' => $this->dioceseId(),
                'date_of_birth' => '2004-01-01',
                'age_category' => 'adult',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasNoErrors();

        $profile = \App\Models\YouthProfile::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->assertSame('2004-01-01', $profile->date_of_birth->toDateString());
        $this->assertSame('youth', $profile->age_category);
    }

    public function test_sidebar_groups_can_fold_and_the_current_group_always_opens(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('youth.learning'))->assertOk();

        foreach (['grow', 'together', 'watch-give', 'you'] as $group) {
            $response->assertSee('data-nav-toggle="'.$group.'" aria-expanded="true" aria-controls="nav-'.$group.'"', false)
                ->assertSee('<div class="nav-items" id="nav-'.$group.'">', false);
        }

        // The page being viewed (My learning, in "Grow") is never hidden by a saved fold.
        $response->assertSee("k!=='grow'", false)
            ->assertSee('data-side-toggle', false);
    }
}
