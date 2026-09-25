<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_youth_is_redirected_home_from_guest_only_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_active_cms_user_is_redirected_to_admin_from_guest_only_pages(): void
    {
        $user = User::factory()->create();

        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'organisation_unit_id' => null,
            'role' => 'events_manager',
            'is_active' => true,
        ]);

        $this->assertTrue($user->hasCmsAccess());

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/admin');
    }

    public function test_inactive_admin_role_does_not_grant_cms_access(): void
    {
        $user = User::factory()->create();

        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'organisation_unit_id' => null,
            'role' => 'events_manager',
            'is_active' => false,
        ]);

        $this->assertFalse($user->hasCmsAccess());

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }
}
