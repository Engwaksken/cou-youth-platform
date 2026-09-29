<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Models\YouthProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_admin_login_rejects_valid_account_without_cms_access(): void
    {
        User::factory()->create([
            'email' => 'youth-only@example.com',
            'password' => Hash::make('StrongPass123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'youth-only@example.com',
            'password' => 'StrongPass123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_specialised_cms_role_cannot_open_system_settings(): void
    {
        $unit = OrganisationUnit::query()->create([
            'type' => 'diocese',
            'name' => 'Test Diocese',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'organisation_unit_id' => $unit->id,
            'role' => 'events_manager',
            'permissions' => [],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_inactive_cms_role_does_not_grant_access(): void
    {
        $unit = OrganisationUnit::query()->create([
            'type' => 'diocese',
            'name' => 'Inactive Role Diocese',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'organisation_unit_id' => $unit->id,
            'role' => 'super_admin',
            'permissions' => [],
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_profile_age_category_is_derived_from_date_of_birth_not_form_input(): void
    {
        $user = User::factory()->create();
        YouthProfile::query()->create([
            'user_id' => $user->id,
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'age_category' => 'youth',
            'interests' => [],
            'talents' => [],
            'skills' => [],
            'ministry_interests' => [],
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'date_of_birth' => now()->subYears(25)->toDateString(),
                'age_category' => 'teen',
                'profile_public' => false,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('youth_profiles', [
            'user_id' => $user->id,
            'age_category' => 'young_adult',
        ]);
    }

    public function test_profile_cannot_change_into_teen_category_without_guardian_consent(): void
    {
        $user = User::factory()->create();
        YouthProfile::query()->create([
            'user_id' => $user->id,
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'age_category' => 'youth',
            'interests' => [],
            'talents' => [],
            'skills' => [],
            'ministry_interests' => [],
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'date_of_birth' => now()->subYears(16)->toDateString(),
                'profile_public' => false,
            ])
            ->assertSessionHasErrors('date_of_birth');

        $this->assertDatabaseHas('youth_profiles', [
            'user_id' => $user->id,
            'age_category' => 'youth',
        ]);
    }
}
