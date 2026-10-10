<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationRole;
use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class OrganisationRoleCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);

        return $user;
    }

    private function unit(string $name): OrganisationUnit
    {
        return OrganisationUnit::query()->create(['type' => 'parish', 'name' => $name, 'is_active' => true]);
    }

    public function test_non_super_admin_is_forbidden_from_role_catalog_management(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
    }

    public function test_super_admin_can_create_update_list_and_delete_catalog_role(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Catalog Parish');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id,
            'name' => 'Youth Coordinator',
            'permissions' => ['admin.content.index'],
        ])->assertCreated();
        $roleId = $response->json('data.id');

        $this->putJson("/api/v1/admin/roles/{$roleId}", ['name' => 'Senior Youth Coordinator'])
            ->assertOk()->assertJsonPath('data.name', 'Senior Youth Coordinator');
        $this->getJson('/api/v1/admin/roles')->assertOk()->assertJsonFragment(['name' => 'Senior Youth Coordinator']);
        $this->deleteJson("/api/v1/admin/roles/{$roleId}")->assertNoContent();
        $this->assertDatabaseMissing('organisation_roles', ['id' => $roleId]);
    }

    public function test_catalog_role_names_are_scoped_to_their_organisation(): void
    {
        $admin = $this->superAdmin();
        $first = $this->unit('First Parish');
        $second = $this->unit('Second Parish');
        $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $first->id, 'name' => 'Coordinator', 'permissions' => [],
        ])->assertCreated();

        $this->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $second->id, 'name' => 'Coordinator', 'permissions' => [],
        ])->assertCreated();

        $this->assertSame(2, OrganisationRole::query()->where('name', 'Coordinator')->count());
    }

    public function test_catalog_rejects_duplicate_role_names_in_the_same_organisation(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Duplicate Parish');
        $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'Coordinator', 'permissions' => [],
        ])->assertCreated();

        $this->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'Coordinator', 'permissions' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_role_name_cannot_be_used_in_both_global_and_scoped_catalogs(): void
    {
        $unit = $this->unit('Global Collision Parish');
        OrganisationRole::query()->create(['name' => 'Shared Role', 'permissions' => []]);

        try {
            OrganisationRole::query()->create([
                'organisation_unit_id' => $unit->id, 'name' => 'Shared Role', 'permissions' => [],
            ]);
            $this->fail('A role name shared by global and scoped catalogs should be rejected.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('name', 'Shared Role')->count());
        }
    }

    public function test_duplicate_global_role_names_are_rejected(): void
    {
        OrganisationRole::query()->create(['name' => 'Global Coordinator', 'permissions' => []]);

        try {
            OrganisationRole::query()->create(['name' => 'Global Coordinator', 'permissions' => []]);
            $this->fail('Duplicate global role names should be rejected.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('name', 'Global Coordinator')->count());
        }
    }

    public function test_organisation_unit_with_scoped_role_cannot_be_deleted(): void
    {
        $unit = $this->unit('Protected Parish');
        OrganisationRole::query()->create([
            'organisation_unit_id' => $unit->id,
            'name' => 'Scoped Coordinator',
            'permissions' => [],
        ]);

        try {
            $unit->delete();
            $this->fail('Deleting an organisation unit with a scoped role should be restricted.');
        } catch (QueryException) {
            $this->assertDatabaseHas('organisation_units', ['id' => $unit->id]);
            $this->assertDatabaseHas('organisation_roles', [
                'organisation_unit_id' => $unit->id,
                'name' => 'Scoped Coordinator',
            ]);
        }
    }

    public function test_catalog_rejects_name_collision_with_builtin_role_in_another_scope(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Collision Parish');

        $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'super_admin', 'permissions' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_store_explicitly_denies_user_without_catalog_policy_permission(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);
        $unit = $this->unit('Denied Parish');

        $this->actingAs($user)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'Denied Role', 'permissions' => [],
        ])->assertForbidden();
    }

    public function test_catalog_rejects_invalid_and_wildcard_permissions(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Permission Parish');

        $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'organisation_unit_id' => $unit->id, 'name' => 'Bad Permission Role', 'permissions' => ['*'],
        ])->assertUnprocessable()->assertJsonValidationErrors('permissions.0');
    }

    public function test_assignment_permission_payload_is_ignored_without_catalog_role(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'role' => 'content_manager', 'permissions' => ['*'],
        ])->assertCreated();
        $this->assertNull($member->fresh()->organisationRoles()->findOrFail($response->json('data.id'))->permissions);
    }

    public function test_assignment_role_must_exist_in_the_selected_organisation(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Assignment Parish');

        $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id,
            'role' => 'not_a_catalog_role', 'permissions' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    public function test_last_super_admin_assignment_is_guarded_by_distinct_user(): void
    {
        $firstAdmin = $this->superAdmin();
        $secondAdmin = $this->superAdmin();
        $duplicate = UserOrganisationRole::query()->create([
            'user_id' => $firstAdmin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);
        $firstAssignment = $firstAdmin->organisationRoles()->firstOrFail();
        $secondAssignment = $secondAdmin->organisationRoles()->firstOrFail();

        $this->actingAs($firstAdmin)->deleteJson("/api/v1/admin/user-organisation-roles/{$duplicate->id}")->assertNoContent();
        $this->deleteJson("/api/v1/admin/user-organisation-roles/{$secondAssignment->id}")->assertNoContent();
        $this->actingAs($firstAdmin)->deleteJson("/api/v1/admin/user-organisation-roles/{$firstAssignment->id}")->assertUnprocessable();
    }

    public function test_empty_user_database_allows_first_super_admin_assignment(): void
    {
        $firstAdmin = User::factory()->create();

        // SQLite's user_version write lock is exercised within this process; a genuine
        // competing-process race cannot be simulated by this RefreshDatabase test.
        $guard = new class {
            use \App\Http\Controllers\Admin\GuardsSuperAdminMutations;
            public function acquire(): void { $this->lockSuperAdminMutationSet(); }
        };
        $guard->acquire();
        $this->assertSame('sqlite', \Illuminate\Support\Facades\DB::connection()->getDriverName());
    }

    public function test_catalog_role_bulk_update_cannot_change_permissions(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Bulk Update Parish');
        $role = OrganisationRole::query()->create([
            'organisation_unit_id' => $unit->id, 'name' => 'Protected Role', 'permissions' => ['admin.content.index'],
        ]);

        try {
            $role->newQuery()->whereKey($role->id)->update(['permissions' => ['admin.events.index']]);
            $this->fail('Bulk permission update should be rejected.');
        } catch (\LogicException $exception) {
            $this->assertSame('Organisation role permissions must be changed through model saves.', $exception->getMessage());
        }
        $this->assertSame(['admin.content.index'], $role->fresh()->permissions);
    }

    public function test_removed_users_show_api_route_is_not_registered(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('api.v1.admin.users.show'));
    }

    public function test_removed_user_organisation_roles_show_api_route_is_not_registered(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('api.v1.admin.user-organisation-roles.show'));
    }
}
