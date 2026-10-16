<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAssignmentDialogsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);

        return $admin;
    }

    public function test_assignment_table_uses_modals_without_inline_forms(): void
    {
        $admin = $this->admin();
        $assignment = UserOrganisationRole::query()->create([
            'user_id' => User::factory()->create()->id, 'role' => 'content_manager', 'is_active' => true,
        ]);
        $response = $this->actingAs($admin)->get(route('admin.user-organisation-roles.index'))->assertOk();
        $response->assertSee('data-user-dialog="create-assignment"', false)
            ->assertSee('data-user-dialog="edit-assignment-'.$assignment->id.'"', false)
            ->assertSee('data-user-dialog="delete-assignment-'.$assignment->id.'"', false)
            ->assertSee('id="assignment-'.$assignment->id.'-organisation"', false)
            ->assertSee('name="is_active" value="0"', false)
            ->assertSee('data-role-scope', false)->assertSee('data-scoped-roles', false);

        preg_match('/<table class="users-table assignments-table">.*?<\/table>/s', $response->getContent(), $table);
        $this->assertNotEmpty($table);
        $this->assertStringNotContainsString('<form', $table[0]);
        $this->assertStringNotContainsString('<select', $table[0]);
    }

    public function test_unchecked_active_assignment_can_be_deactivated(): void
    {
        $admin = $this->admin();
        $assignment = UserOrganisationRole::query()->create([
            'user_id' => User::factory()->create()->id, 'role' => 'content_manager', 'is_active' => true,
        ]);

        $this->actingAs($admin)->from(route('admin.user-organisation-roles.index'))
            ->put(route('admin.user-organisation-roles.update', $assignment), [
                'role' => 'content_manager', 'organisation_unit_id' => '', 'is_active' => '0',
                '_assignment_form' => 'edit', '_assignment_id' => $assignment->id,
            ])->assertRedirect(route('admin.user-organisation-roles.index'))->assertSessionHasNoErrors();

        $this->assertFalse($assignment->refresh()->is_active);
    }

    public function test_failed_create_reopens_assignment_modal_and_retains_inactive_status(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->from(route('admin.user-organisation-roles.index'))
            ->post(route('admin.user-organisation-roles.store'), [
                'user_id' => '', 'role' => 'content_manager', 'is_active' => '0',
                '_assignment_form' => 'create',
            ])->assertRedirect(route('admin.user-organisation-roles.index'))->assertSessionHasErrors('user_id');

        $response = $this->get(route('admin.user-organisation-roles.index'))->assertOk();
        preg_match('/<dialog[^>]*id="create-assignment".*?<\/dialog>/s', $response->getContent(), $dialog);
        $this->assertNotEmpty($dialog);
        $this->assertStringContainsString('data-auto-open', $dialog[0]);
        $this->assertMatchesRegularExpression('/value="content_manager"\s+selected/', $dialog[0]);
        $this->assertDoesNotMatchRegularExpression('/id="create-active"[^>]*checked/', $dialog[0]);
    }

    public function test_failed_edit_reopens_only_the_correct_assignment(): void
    {
        $admin = $this->admin();
        $assignment = UserOrganisationRole::query()->create([
            'user_id' => User::factory()->create()->id, 'role' => 'content_manager', 'is_active' => true,
        ]);
        $this->actingAs($admin)->from(route('admin.user-organisation-roles.index'))
            ->put(route('admin.user-organisation-roles.update', $assignment), [
                'role' => '', 'is_active' => '0',
                '_assignment_form' => 'edit', '_assignment_id' => $assignment->id,
            ])->assertRedirect(route('admin.user-organisation-roles.index'))->assertSessionHasErrors('role');

        $response = $this->get(route('admin.user-organisation-roles.index'))->assertOk();
        preg_match('/<dialog[^>]*id="edit-assignment-'.$assignment->id.'".*?<\/dialog>/s', $response->getContent(), $dialog);
        $this->assertNotEmpty($dialog);
        $this->assertStringContainsString('data-auto-open', $dialog[0]);
        $this->assertSame(1, substr_count($response->getContent(), ' data-auto-open'));
        $this->assertSame('content_manager', $assignment->refresh()->role);
    }

    public function test_remove_modal_preserves_last_super_admin_safeguard(): void
    {
        $admin = $this->admin();
        $assignment = $admin->organisationRoles()->firstOrFail();
        $this->actingAs($admin)->from(route('admin.user-organisation-roles.index'))
            ->delete(route('admin.user-organisation-roles.destroy', $assignment), [
                '_assignment_form' => 'delete', '_assignment_id' => $assignment->id,
            ])->assertStatus(422);

        $response = $this->get(route('admin.user-organisation-roles.index'))->assertOk();
        preg_match('/<dialog[^>]*id="delete-assignment-'.$assignment->id.'".*?<\/dialog>/s', $response->getContent(), $dialog);
        $this->assertNotEmpty($dialog);
        $this->assertStringContainsString('Remove assignment?', $dialog[0]);
        $this->assertDatabaseHas('user_organisation_roles', ['id' => $assignment->id, 'is_active' => true]);
    }
}
