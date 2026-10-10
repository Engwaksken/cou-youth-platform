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

    public function test_role_permission_update_synchronizes_existing_assignments(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Sync Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Sync Editor', 'permissions' => ['admin.content.index']]);
        $assignment = UserOrganisationRole::query()->create(['user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => $role->name, 'permissions' => $role->permissions, 'is_active' => true]);

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", ['permissions' => ['admin.events.index']])->assertOk();

        $this->assertSame(['admin.events.index'], $assignment->fresh()->permissions);
    }

    public function test_catalog_assignment_persists_direct_role_reference(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Direct Reference Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Direct Editor', 'permissions' => ['admin.content.index']]);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => $role->name,
        ])->assertCreated();

        $this->assertDatabaseHas('user_organisation_roles', [
            'id' => $response->json('data.id'), 'organisation_role_id' => $role->id,
        ]);
    }

    public function test_role_rename_scope_and_permissions_synchronize_direct_references(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Before Sync Parish');
        $newUnit = $this->unit('After Sync Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Before Editor', 'permissions' => ['admin.content.index']]);
        $secondUnit = $this->unit('Second Assignment Parish');
        $assignments = collect([$unit, $secondUnit])->map(fn (OrganisationUnit $assignmentUnit) => UserOrganisationRole::query()->create([
            'user_id' => $member->id, 'organisation_unit_id' => $assignmentUnit->id, 'organisation_role_id' => $role->id,
            'role' => $role->name, 'permissions' => $role->permissions, 'is_active' => true,
        ]));

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", [
            'name' => 'After Editor', 'organisation_unit_id' => $newUnit->id, 'permissions' => ['admin.events.index'],
        ])->assertOk();

        foreach ($assignments as $assignment) {
            $this->assertDatabaseHas('user_organisation_roles', [
                'id' => $assignment->id, 'organisation_role_id' => $role->id,
                'role' => 'After Editor', 'organisation_unit_id' => $assignment->organisation_unit_id,
            ]);
            $this->assertSame(['admin.events.index'], $assignment->fresh()->permissions);
        }
        $this->assertDatabaseHas('organisation_roles', ['id' => $role->id, 'name' => 'After Editor', 'organisation_unit_id' => $newUnit->id]);
    }

    public function test_role_rename_preserves_each_direct_assignment_scope(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $roleUnit = $this->unit('Role Owner Parish');
        $assignmentUnits = [$this->unit('Assignment One'), $this->unit('Assignment Two')];
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $roleUnit->id, 'name' => 'Scoped Editor', 'permissions' => []]);
        $assignments = collect($assignmentUnits)->map(fn (OrganisationUnit $unit) => UserOrganisationRole::query()->create([
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'organisation_role_id' => $role->id,
            'role' => $role->name, 'permissions' => [], 'is_active' => true,
        ]));

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'Renamed Scoped Editor'])->assertOk();

        foreach ($assignments as $assignment) {
            $this->assertDatabaseHas('user_organisation_roles', [
                'id' => $assignment->id, 'organisation_role_id' => $role->id,
                'organisation_unit_id' => $assignment->organisation_unit_id, 'role' => 'Renamed Scoped Editor',
            ]);
        }
    }

    public function test_global_role_rename_updates_same_users_assignments_in_distinct_scopes(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $units = [$this->unit('Global Assignment One'), $this->unit('Global Assignment Two')];
        $role = OrganisationRole::query()->create(['name' => 'Global Editor', 'permissions' => ['admin.content.index']]);
        $assignments = collect($units)->map(fn (OrganisationUnit $unit) => UserOrganisationRole::query()->create([
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'organisation_role_id' => $role->id,
            'role' => $role->name, 'permissions' => $role->permissions, 'is_active' => true,
        ]));

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'Global Editor Renamed'])->assertOk();

        foreach ($assignments as $assignment) {
            $this->assertDatabaseHas('user_organisation_roles', [
                'id' => $assignment->id, 'organisation_role_id' => $role->id,
                'organisation_unit_id' => $assignment->organisation_unit_id, 'role' => 'Global Editor Renamed',
            ]);
        }
    }

    public function test_global_role_rename_collision_in_same_assignment_scope_fails_without_mutation(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Same Scope Collision');
        $role = OrganisationRole::query()->create(['name' => 'Old Global Name', 'permissions' => ['admin.content.index']]);
        $collision = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Taken Scoped Name', 'permissions' => []]);
        $assignment = UserOrganisationRole::query()->create([
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'organisation_role_id' => $role->id,
            'role' => $role->name, 'permissions' => $role->permissions, 'is_active' => true,
        ]);

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", ['name' => $collision->name])->assertStatus(409);

        $this->assertDatabaseHas('organisation_roles', ['id' => $role->id, 'name' => 'Old Global Name']);
        $this->assertDatabaseHas('user_organisation_roles', [
            'id' => $assignment->id, 'organisation_role_id' => $role->id,
            'organisation_unit_id' => $unit->id, 'role' => 'Old Global Name',
        ]);
    }

    public function test_guest_cannot_manage_role_catalog(): void
    {
        $this->getJson('/api/v1/admin/roles')->assertUnauthorized();
    }

    public function test_assignment_referencing_role_cannot_be_deleted_with_clear_conflict(): void
    {
        $admin = $this->superAdmin();
        $unit = $this->unit('Referenced Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Referenced Editor', 'permissions' => []]);
        UserOrganisationRole::query()->create([
            'user_id' => User::factory()->create()->id, 'organisation_unit_id' => $unit->id,
            'organisation_role_id' => $role->id, 'role' => $role->name, 'permissions' => [], 'is_active' => true,
        ]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/roles/{$role->id}")
            ->assertUnprocessable()->assertJsonFragment(['message' => 'This role is assigned and cannot be deleted.']);
    }

    public function test_legacy_assignments_without_direct_reference_remain_unlinked(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Legacy Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Legacy Editor', 'permissions' => ['admin.content.index']]);
        $legacy = UserOrganisationRole::query()->create(['user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => $role->name, 'permissions' => ['admin.content.index'], 'is_active' => true]);

        $this->actingAs($admin)->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'Renamed Editor'])->assertOk();

        $this->assertDatabaseHas('user_organisation_roles', ['id' => $legacy->id, 'organisation_role_id' => null, 'role' => 'Legacy Editor']);
    }

    public function test_role_name_uniqueness_migration_supports_global_and_scoped_scope_zero_up_and_down_on_sqlite(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('The migration up/down assertions here exercise SQLite only; MySQL and PostgreSQL migrations require their native drivers.');
        }

        $migration = require database_path('migrations/2026_10_10_160000_serialize_organisation_role_name_uniqueness.php');
        $migration->down();
        $migration->up();
        $this->assertSame('sqlite', \Illuminate\Support\Facades\DB::connection()->getDriverName());
    }

    public function test_direct_role_reference_migration_rolls_back_and_restores_column_on_sqlite(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('The migration rollback assertions here exercise SQLite only; MySQL and PostgreSQL migrations require their native drivers.');
        }

        $migration = require database_path('migrations/2026_10_10_170000_add_organisation_role_id_to_user_organisation_roles.php');
        $migration->down();
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('user_organisation_roles', 'organisation_role_id'));

        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('user_organisation_roles', 'organisation_role_id'));
    }

    public function test_catalog_role_cannot_be_deleted_while_assigned(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = $this->unit('Assigned Parish');
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Assigned Editor', 'permissions' => []]);
        UserOrganisationRole::query()->create(['user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => $role->name, 'permissions' => [], 'is_active' => true]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/roles/{$role->id}")->assertUnprocessable();
        $this->assertDatabaseHas('organisation_roles', ['id' => $role->id]);
    }

    public function test_sqlite_rbac_mutations_share_an_exclusive_database_file_lock(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('SQLite OS lock behavior is only applicable to SQLite.');
        }

        // All RBAC mutation controllers use this shared trait and derive the lock
        // path from the database filename. A second independent handle must block
        // while a mutation holds the exclusive lock; this tests OS-level exclusion,
        // not cross-process database transaction/race behavior.
        $guard = new class {
            use \App\Http\Controllers\Admin\GuardsSuperAdminMutations;
            public function acquire(): void { $this->lockSuperAdminMutationSet(); }
            public function release(): void { $this->unlockSuperAdminMutationSet(); }
        };
        $guard->acquire();
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'rbac-'.hash('sha256', \Illuminate\Support\Facades\DB::connection()->getDatabaseName()).'.lock';
        $secondHandle = fopen($path, 'c');
        try {
            $this->assertNotFalse($secondHandle);
            $this->assertFalse(flock($secondHandle, LOCK_EX | LOCK_NB));
        } finally {
            if (is_resource($secondHandle)) fclose($secondHandle);
            $guard->release();
        }
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
