<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserDialogsTest extends TestCase
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

    public function test_users_table_has_dialog_actions_instead_of_inline_forms(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $response = $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $response->assertSee('data-user-dialog="create-user"', false)
            ->assertSee('data-user-dialog="edit-user-'.$user->id.'"', false)
            ->assertSee('data-user-dialog="delete-user-'.$user->id.'"', false)
            ->assertSee('id="edit-user-'.$user->id.'"', false)
            ->assertSee('id="delete-user-'.$user->id.'"', false);

        preg_match('/<table class="users-table">.*?<\/table>/s', $response->getContent(), $table);
        $this->assertNotEmpty($table);
        $this->assertStringNotContainsString('<form', $table[0]);
        $this->assertStringNotContainsString('<input', $table[0]);
    }

    public function test_blank_password_in_edit_form_keeps_existing_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $password = $user->password;

        $this->actingAs($admin)->from(route('admin.users.index'))
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated account', 'email' => $user->email,
                'password' => '', 'password_confirmation' => '',
                '_user_form' => 'edit', '_user_id' => $user->id,
            ])->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();

        $this->assertSame('Updated account', $user->refresh()->name);
        $this->assertSame($password, $user->password);
    }

    public function test_blank_password_in_json_update_keeps_existing_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $password = $user->password;

        $this->actingAs($admin)->putJson(route('admin.users.update', $user), [
            'name' => 'Updated via JSON', 'password' => '', 'password_confirmation' => '',
        ])->assertOk();

        $this->assertSame($password, $user->refresh()->password);
    }

    public function test_failed_edit_reopens_only_its_dialog_and_restores_input(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $this->actingAs($admin)->from(route('admin.users.index'))
            ->put(route('admin.users.update', $user), [
                'name' => 'Unsaved edit', 'email' => 'not-an-email',
                '_user_form' => 'edit', '_user_id' => $user->id,
            ])->assertRedirect(route('admin.users.index'))->assertSessionHasErrors('email');

        $response = $this->get(route('admin.users.index'))->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), ' data-auto-open'));
        $response->assertSee('id="edit-user-'.$user->id.'" aria-labelledby="edit-user-title-'.$user->id.'"  data-auto-open', false)
            ->assertSee('value="Unsaved edit"', false)
            ->assertSee('value="not-an-email"', false);
        $this->assertNotSame('Unsaved edit', $user->refresh()->name);
    }

    public function test_failed_create_reopens_add_user_dialog(): void
    {
        $this->actingAs($this->admin())->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'New account', 'email' => 'invalid', '_user_form' => 'create',
                'password' => 'password123', 'password_confirmation' => 'password123',
            ])->assertRedirect(route('admin.users.index'))->assertSessionHasErrors('email');

        $response = $this->get(route('admin.users.index'))->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), ' data-auto-open'));
        $response->assertSee('id="create-user" aria-labelledby="create-user-title"  data-auto-open', false)
            ->assertSee('value="New account"', false);
        $response->assertDontSee('value="password123"', false);
    }
}
