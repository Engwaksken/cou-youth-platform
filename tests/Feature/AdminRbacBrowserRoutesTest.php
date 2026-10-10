<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Models\OrganisationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRbacBrowserRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);

        return $user;
    }

    public function test_guest_is_redirected_to_login_from_rbac_browser_routes(): void
    {
        foreach (['/admin/users', '/admin/roles', '/admin/user-organisation-roles'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_non_super_admin_is_forbidden_from_rbac_browser_routes(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);

        foreach (['/admin/users', '/admin/roles', '/admin/user-organisation-roles'] as $path) {
            $this->actingAs($user)->get($path)->assertForbidden();
        }
    }

    public function test_super_admin_browser_route_indexes_render_their_expected_views(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertViewIs('admin.users.index');
        $this->actingAs($admin)->get('/admin/roles')->assertOk()->assertViewIs('admin.organisation-roles.index');
        $this->actingAs($admin)->get('/admin/user-organisation-roles')->assertOk()->assertViewIs('admin.user-organisation-roles.index');
    }

    public function test_browser_routes_have_names_and_supply_form_data(): void
    {
        $admin = $this->superAdmin();

        $this->assertSame('/admin/users', parse_url(route('admin.users.index'), PHP_URL_PATH));
        $this->assertSame('/admin/roles', parse_url(route('admin.roles.index'), PHP_URL_PATH));
        $this->assertSame('/admin/user-organisation-roles', parse_url(route('admin.user-organisation-roles.index'), PHP_URL_PATH));
        $unit = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Form Parish', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/roles')->assertViewHas('organisationUnits', fn ($units) => $units->contains('id', $unit->id));
        $this->actingAs($admin)->get('/admin/user-organisation-roles')->assertViewHasAll(['assignments', 'users', 'organisationUnits', 'roles']);
    }

    public function test_browser_role_form_redirects_with_success_flash(): void
    {
        $admin = $this->superAdmin();
        $unit = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Browser Parish', 'is_active' => true]);

        $this->actingAs($admin)->from('/admin/roles')->post('/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'Browser role', 'permissions' => ['admin.users.index'],
        ])->assertRedirect('/admin/roles')->assertSessionHas('success', 'Organisation role created.');
    }

    public function test_invalid_user_creation_redirects_back_with_validation_errors(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from('/admin/users')->post('/admin/users', [])->assertRedirect('/admin/users')->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_invalid_role_creation_redirects_back_with_validation_errors(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from('/admin/roles')->post('/admin/roles', [])->assertRedirect('/admin/roles')->assertSessionHasErrors(['organisation_unit_id', 'name']);
    }

    public function test_invalid_assignment_creation_redirects_back_with_validation_errors(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from('/admin/user-organisation-roles')->post('/admin/user-organisation-roles', [])->assertRedirect('/admin/user-organisation-roles')->assertSessionHasErrors(['user_id', 'role']);
    }

    public function test_browser_assignment_create_update_and_delete_redirect_with_success_flash(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        $this->actingAs($admin)->from('/admin/user-organisation-roles')->post('/admin/user-organisation-roles', [
            'user_id' => $member->id, 'role' => 'content_manager', 'permissions' => ['admin.content.index'],
        ])->assertRedirect('/admin/user-organisation-roles')->assertSessionHas('success', 'Role assignment created.');

        $assignment = $member->fresh()->organisationRoles()->firstOrFail();
        $this->put("/admin/user-organisation-roles/{$assignment->id}", ['role' => 'events_manager'])
            ->assertRedirect('/admin/user-organisation-roles')->assertSessionHas('success', 'Role assignment updated.');

        $this->delete("/admin/user-organisation-roles/{$assignment->id}")
            ->assertRedirect('/admin/user-organisation-roles')->assertSessionHas('success', 'Role assignment removed.');
    }

    public function test_browser_user_crud_completes_over_the_web_routes(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from('/admin/users')->post('/admin/users', [
            'name' => 'Web Created User', 'email' => 'web-created@example.test',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect('/admin/users');

        $member = User::query()->where('email', 'web-created@example.test')->firstOrFail();
        $this->put("/admin/users/{$member->id}", ['name' => 'Web Updated User', 'email' => $member->email])
            ->assertRedirect('/admin/users');
        $this->assertSame('Web Updated User', $member->fresh()->name);

        $this->delete("/admin/users/{$member->id}")->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_assignment_form_role_options_match_the_selected_organisation_scope(): void
    {
        $admin = $this->superAdmin();
        $selected = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Selected Scope', 'is_active' => true]);
        $other = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Other Scope', 'is_active' => true]);
        $selectedRole = \App\Models\OrganisationRole::query()->create([
            'organisation_unit_id' => $selected->id, 'name' => 'Selected Role', 'permissions' => ['admin.content.index'],
        ]);
        $otherRole = \App\Models\OrganisationRole::query()->create([
            'organisation_unit_id' => $other->id, 'name' => 'Other Role', 'permissions' => ['admin.events.index'],
        ]);

        $this->actingAs($admin)->get('/admin/user-organisation-roles?organisation_unit_id='.$selected->id)
            ->assertOk()->assertViewHas('roles', function ($roles) use ($selectedRole, $otherRole): bool {
                return $roles->contains('id', $selectedRole->id) && ! $roles->contains('id', $otherRole->id);
            });
    }

    public function test_removed_role_show_route_is_not_registered(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.roles.show'));
    }
}
