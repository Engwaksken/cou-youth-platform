<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Models\OrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);
        return $user;
    }

    public function test_guest_cannot_access_admin_user_crud(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_non_super_admin_is_forbidden_from_user_management(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_non_super_admin_module_permissions_do_not_allow_updating_another_user(): void
    {
        $target = User::factory()->create();
        $permissions = [
            'admin.content.index', 'admin.events.index', 'admin.youth.index',
            'admin.reports.index', 'admin.dashboard',
        ];

        foreach ($permissions as $index => $permission) {
            $user = User::factory()->create();
            UserOrganisationRole::query()->create([
                'user_id' => $user->id,
                'role' => 'module_manager_'.$index,
                'permissions' => [$permission],
                'is_active' => true,
            ]);

            $this->actingAs($user)->putJson("/api/v1/admin/users/{$target->id}", ['name' => 'Tampered'])
                ->assertForbidden();
        }

        $this->assertNotSame('Tampered', $target->fresh()->name);
    }

    public function test_super_admin_can_create_update_and_delete_a_user(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->postJson('/api/v1/admin/users', [
            'name' => 'Created User', 'email' => 'created@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertCreated();

        $user = User::query()->where('email', 'created@example.test')->firstOrFail();
        $this->actingAs($admin)->putJson("/api/v1/admin/users/{$user->id}", ['name' => 'Updated User'])->assertOk();
        $this->assertSame('Updated User', $user->fresh()->name);
        $this->actingAs($admin)->deleteJson("/api/v1/admin/users/{$user->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_creation_validates_required_and_unique_fields(): void
    {
        $admin = $this->superAdmin();
        $existing = User::factory()->create();

        $this->actingAs($admin)->postJson('/api/v1/admin/users', [
            'name' => 'Duplicate', 'email' => $existing->email, 'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_last_active_super_admin_user_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->deleteJson("/api/v1/admin/users/{$admin->id}")->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_can_create_assignment_with_permissions(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Test Parish', 'is_active' => true]);

        $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => 'content_manager',
            'permissions' => ['admin.content.index'], 'is_active' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('user_organisation_roles', [
            'user_id' => $member->id, 'organisation_unit_id' => $unit->id, 'role' => 'content_manager',
        ]);
        $this->assertNull($member->fresh()->organisationRoles()->firstOrFail()->permissions);
    }

    public function test_catalog_assignment_permissions_are_authoritative_on_create_and_update(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $unit = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Scoped Parish', 'is_active' => true]);
        OrganisationRole::query()->create([
            'organisation_unit_id' => $unit->id,
            'name' => 'Parish Editor',
            'permissions' => ['admin.content.index'],
        ]);
        OrganisationRole::query()->create([
            'organisation_unit_id' => $unit->id,
            'name' => 'Parish Events',
            'permissions' => ['admin.events.index'],
        ]);

        $created = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id,
            'organisation_unit_id' => $unit->id,
            'role' => 'Parish Editor',
            'permissions' => ['admin.users.destroy'],
        ])->assertCreated();
        $assignmentId = $created->json('data.id');
        $this->assertSame(['admin.content.index'], $member->fresh()->organisationRoles()->findOrFail($assignmentId)->permissions);

        $this->putJson("/api/v1/admin/user-organisation-roles/{$assignmentId}", [
            'role' => 'Parish Events',
            'permissions' => ['admin.users.destroy'],
        ])->assertOk();
        $this->assertSame(['admin.events.index'], $member->fresh()->organisationRoles()->findOrFail($assignmentId)->permissions);
    }

    public function test_catalog_assignment_rejects_role_scoped_to_another_organisation_on_create_and_update(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $first = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'First Scope', 'is_active' => true]);
        $second = OrganisationUnit::query()->create(['type' => 'parish', 'name' => 'Second Scope', 'is_active' => true]);
        OrganisationRole::query()->create(['organisation_unit_id' => $first->id, 'name' => 'Scoped Editor', 'permissions' => ['admin.content.index']]);

        $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'organisation_unit_id' => $second->id, 'role' => 'Scoped Editor',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $assignment = UserOrganisationRole::query()->create([
            'user_id' => $member->id, 'organisation_unit_id' => $second->id,
            'role' => 'events_manager', 'is_active' => true,
        ]);
        $this->putJson("/api/v1/admin/user-organisation-roles/{$assignment->id}", ['role' => 'Scoped Editor'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    public function test_uncatalogued_legacy_builtin_role_is_accepted_and_retains_assignment_permissions(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();
        $response = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id,
            'role' => 'content_manager',
            'permissions' => ['admin.content.index'],
        ])->assertCreated();

        $assignment = $member->fresh()->organisationRoles()->findOrFail($response->json('data.id'));
        $this->assertSame('content_manager', $assignment->role);
    }

    public function test_api_role_show_endpoint_is_not_supported(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->getJson('/api/v1/admin/roles/1')->assertMethodNotAllowed();
    }

    public function test_assignment_validation_rejects_invalid_role_and_permission(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        $this->actingAs($admin)->post('/admin/user-organisation-roles', [
            'user_id' => $member->id, 'role' => 'not_a_role', 'permissions' => ['invalid permission'],
        ])->assertSessionHasErrors('role');
    }

    public function test_last_active_super_admin_assignment_cannot_be_removed(): void
    {
        $admin = $this->superAdmin();
        $assignment = $admin->organisationRoles()->firstOrFail();

        $this->actingAs($admin)->delete("/admin/user-organisation-roles/{$assignment->id}")->assertUnprocessable();
        $this->assertDatabaseHas('user_organisation_roles', ['id' => $assignment->id]);
    }

    public function test_last_active_super_admin_cannot_be_demoted_by_role_mutation(): void
    {
        $admin = $this->superAdmin();
        $assignment = $admin->organisationRoles()->firstOrFail();

        $this->actingAs($admin)->putJson("/api/v1/admin/user-organisation-roles/{$assignment->id}", [
            'role' => 'events_manager',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('user_organisation_roles', ['id' => $assignment->id, 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_api_assignment_crud_uses_json_resource_and_status_codes(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        $created = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
            'user_id' => $member->id, 'role' => 'content_manager', 'permissions' => ['admin.content.index'], 'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.user_id', $member->id)->assertJsonPath('data.role', 'content_manager');
        $id = $created->json('data.id');

        $listed = $this->actingAs($admin)->getJson('/api/v1/admin/user-organisation-roles')->assertOk();
        $this->assertContains($id, array_column($listed->json('data'), 'id'));
        $this->actingAs($admin)->putJson("/api/v1/admin/user-organisation-roles/{$id}", ['role' => 'events_manager'])
            ->assertOk()->assertJsonPath('data.role', 'events_manager');
        $this->actingAs($admin)->deleteJson("/api/v1/admin/user-organisation-roles/{$id}")->assertNoContent();
    }

    public function test_api_ignores_permission_payload_for_uncatalogued_builtin_roles(): void
    {
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        foreach (['admin.not-real.index', '*', 'admin.*'] as $permission) {
            $response = $this->actingAs($admin)->postJson('/api/v1/admin/user-organisation-roles', [
                'user_id' => $member->id, 'role' => 'content_manager', 'permissions' => [$permission],
            ])->assertCreated();
            $this->assertNull($member->fresh()->organisationRoles()->findOrFail($response->json('data.id'))->permissions);
        }
    }

    public function test_api_admin_operations_require_authorization(): void
    {
        $this->getJson('/api/v1/admin/user-organisation-roles')->assertUnauthorized();
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);
        $this->actingAs($user)->getJson('/api/v1/admin/user-organisation-roles')->assertForbidden();
    }

    public function test_super_admin_assignment_can_be_removed_when_another_distinct_active_admin_exists(): void
    {
        $admin = $this->superAdmin();
        $another = $this->superAdmin();
        $assignment = $admin->organisationRoles()->firstOrFail();

        $this->actingAs($admin)->deleteJson("/api/v1/admin/user-organisation-roles/{$assignment->id}")->assertNoContent();
        $this->assertTrue($another->organisationRoles()->where('role', 'super_admin')->where('is_active', true)->exists());
    }

    public function test_super_admin_assignment_can_be_deactivated_when_same_user_has_another_active_assignment(): void
    {
        $admin = $this->superAdmin();
        $assignment = $admin->organisationRoles()->firstOrFail();
        $duplicate = UserOrganisationRole::query()->create(['user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true]);

        $this->actingAs($admin)->putJson("/api/v1/admin/user-organisation-roles/{$assignment->id}", ['is_active' => false])->assertOk();
        $this->assertDatabaseHas('user_organisation_roles', ['id' => $duplicate->id, 'is_active' => true]);
    }

    public function test_non_super_admin_cannot_manage_role_assignments(): void
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'events_manager', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/user-organisation-roles')->assertForbidden();
    }
}
