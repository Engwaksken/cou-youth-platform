<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserCreateDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);

        return $user;
    }

    private function diocese(string $name = 'Kigezi Diocese'): OrganisationUnit
    {
        return OrganisationUnit::query()->where('type', 'diocese')->where('name', $name)->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Staff',
            'email' => 'new-staff@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    public function test_create_form_shows_diocese_phone_location_and_role_fields(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/users')->assertOk()
            ->assertSee('name="phone"', false)
            ->assertSee('name="location"', false)
            ->assertSee('name="diocese_id"', false)
            ->assertSee('name="role"', false)
            ->assertSee('Diocesan Admin');
    }

    public function test_new_user_is_saved_with_details_and_a_diocese_scoped_role(): void
    {
        $diocese = $this->diocese();

        $this->actingAs($this->superAdmin())->from('/admin/users')->post('/admin/users', $this->payload([
            'phone' => '+256 772 123456',
            'location' => 'Kabale',
            'diocese_id' => $diocese->id,
            'role' => 'diocesan_admin',
        ]))->assertRedirect('/admin/users')->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'new-staff@example.test')->firstOrFail();
        $this->assertSame('+256 772 123456', $user->phone);
        $this->assertSame('Kabale', $user->location);
        $this->assertSame($diocese->id, $user->diocese_id);
        $this->assertDatabaseHas('user_organisation_roles', [
            'user_id' => $user->id, 'role' => 'diocesan_admin', 'organisation_unit_id' => $diocese->id, 'is_active' => true,
        ]);
    }

    public function test_platform_wide_role_is_not_scoped_to_the_diocese(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/users', $this->payload([
            'diocese_id' => $this->diocese()->id, 'role' => 'provincial_admin',
        ]))->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'new-staff@example.test')->firstOrFail();
        $this->assertDatabaseHas('user_organisation_roles', ['user_id' => $user->id, 'role' => 'provincial_admin', 'organisation_unit_id' => null]);
    }

    public function test_role_and_contact_fields_are_optional(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/users', $this->payload())->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'new-staff@example.test')->firstOrFail();
        $this->assertFalse($user->organisationRoles()->exists());
    }

    public function test_diocese_role_requires_a_diocese_and_invalid_input_is_rejected(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from('/admin/users')->post('/admin/users', $this->payload(['role' => 'diocesan_admin']))
            ->assertSessionHasErrors('role');
        $this->post('/admin/users', $this->payload(['role' => 'not_a_role', 'diocese_id' => $this->diocese()->id]))
            ->assertSessionHasErrors('role');
        $this->post('/admin/users', $this->payload(['phone' => 'call me']))->assertSessionHasErrors('phone');
        $this->post('/admin/users', $this->payload(['diocese_id' => 999999]))->assertSessionHasErrors('diocese_id');

        $this->assertDatabaseMissing('users', ['email' => 'new-staff@example.test']);
    }

    public function test_edit_updates_details_and_list_filters_by_user_diocese(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $diocese = $this->diocese();

        $this->actingAs($admin)->put("/admin/users/{$member->id}", [
            'name' => $member->name, 'email' => $member->email,
            'phone' => '0772123456', 'location' => 'Mbarara', 'diocese_id' => $diocese->id,
        ])->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame('Mbarara', $member->location);
        $this->assertSame($diocese->id, $member->diocese_id);

        $this->get('/admin/users?diocese_id='.$diocese->id)->assertOk()->assertSee($member->email);
    }
}
