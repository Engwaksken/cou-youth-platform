<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Models\OrganisationRole;
use App\Models\OrganisationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessPagesTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        return $user;
    }

    public function test_super_admin_sees_access_navigation_and_the_user_creation_form(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/users')->assertOk()
            ->assertSee('User &amp; Access', false)
            ->assertSee('/admin/users', false)
            ->assertSee('/admin/user-organisation-roles', false)
            ->assertSee('/admin/roles', false)
            ->assertSee('Add user')
            ->assertSee('name="email"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Create user');
    }

    public function test_role_assignment_page_renders_required_controls(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/user-organisation-roles')->assertOk()
            ->assertSee('Assign role')
            ->assertSee('name="user_id"', false)
            ->assertSee('name="organisation_unit_id"', false)
            ->assertSee('name="role"', false)
            ->assertSee('name="is_active"', false)
            ->assertSee('Create assignment');
    }

    public function test_role_options_and_client_filter_rules_preserve_global_only_scope_behavior(): void
    {
        $admin = $this->superAdmin();
        $unit = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Role Scope', 'is_active' => true]);
        $global = OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => 'Global Named Role', 'permissions' => ['admin.content.index']]);
        $scoped = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Unit Named Role', 'permissions' => ['admin.events.index']]);

        $response = $this->actingAs($admin)->get('/admin/user-organisation-roles?organisation_unit_id='.$unit->id);

        $response->assertOk()
            ->assertSee('value="'.$global->name.'"', false)
            ->assertSee('data-organisation-id=""', false)
            ->assertSee('value="'.$scoped->name.'"', false)
            ->assertSee('data-organisation-id="'.$unit->id.'"', false)
            ->assertSee("const matches = roleScope === '' || roleScope === selectedScope;", false)
            ->assertSee('option.hidden = !matches && !preserveInitialAssignment;', false)
            ->assertSee('option.disabled = !matches && !preserveInitialAssignment;', false);

        $this->actingAs($admin)->get('/admin/user-organisation-roles')
            ->assertOk()
            ->assertSee('value="'.$global->name.'"', false)
            ->assertSee('data-organisation-id=""', false)
            ->assertSee('value="'.$scoped->name.'"', false)
            ->assertSee('data-organisation-id="'.$unit->id.'"', false)
            ->assertSee("const matches = roleScope === '' || roleScope === selectedScope;", false);
    }

    public function test_user_delete_confirmation_safely_represents_dangerous_user_name(): void
    {
        $admin = $this->superAdmin();
        $user = User::factory()->create(['name' => "</script><img src=x onerror=alert(1)> ' \" \\ &"]);

        $this->actingAs($admin)->get('/admin/users')->assertOk()
            ->assertSee(\Illuminate\Support\Js::from($user->name)->toHtml(), false)
            ->assertDontSee("confirm('Remove ' + ".$user->name, false);
    }

    public function test_named_role_catalog_renders_role_and_permission_controls(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/roles')->assertOk()
            ->assertSee('Add named role')
            ->assertSee('name="organisation_unit_id"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="permissions[]"', false)
            ->assertSee('Create role');
    }

    public function test_non_super_admin_does_not_see_user_access_navigation(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $user->id,
            'role' => 'events_manager',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin')->assertOk()
            ->assertDontSee('User &amp; Access', false)
            ->assertDontSee('/admin/users', false)
            ->assertDontSee('/admin/user-organisation-roles', false)
            ->assertDontSee('/admin/roles', false);
    }
}
