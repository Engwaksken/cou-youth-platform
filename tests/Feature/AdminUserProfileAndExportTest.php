<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserProfileAndExportTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);

        return $user;
    }

    private function cmsUser(): User
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'content_manager', 'is_active' => true]);

        return $user;
    }

    private function diocese(): OrganisationUnit
    {
        return OrganisationUnit::query()->where('type', 'diocese')->where('name', 'Kigezi Diocese')->firstOrFail();
    }

    public function test_users_page_shows_statistics_and_export_links(): void
    {
        $admin = $this->superAdmin();
        User::factory()->count(2)->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 3 && $stats['staff'] === 1 && $stats['new'] === 3)
            ->assertSee('Total users')->assertSee('Admin staff')->assertSee('Youth members')
            ->assertSee(route('admin.users.export.csv'), false)
            ->assertSee(route('admin.users.export.pdf'), false)
            ->assertSee(route('admin.users.show', $admin), false);
    }

    public function test_admin_can_view_and_update_a_user_profile_with_photo(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $member = User::factory()->create(['name' => 'Profile Member']);

        $this->actingAs($admin)->get("/admin/users/{$member->id}")->assertOk()
            ->assertViewIs('admin.users.show')->assertSee('Profile Member')->assertSee('name="photo"', false);

        $this->from("/admin/users/{$member->id}")->put("/admin/users/{$member->id}", [
            'name' => 'Profile Member', 'email' => $member->email, 'phone' => '+256 772 000111', 'location' => 'Kabale',
            'diocese_id' => $this->diocese()->id, 'photo' => UploadedFile::fake()->image('me.jpg', 300, 300),
        ])->assertRedirect("/admin/users/{$member->id}")->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertNotNull($member->profile_photo);
        Storage::disk('public')->assertExists($member->profile_photo);
        $this->assertSame('Kabale', $member->location);

        $old = $member->profile_photo;
        $this->put("/admin/users/{$member->id}", ['remove_photo' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($member->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_photo_must_be_an_image(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $member = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$member->id}", [
            'photo' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('photo');
        $this->assertNull($member->fresh()->profile_photo);
    }

    public function test_non_super_admin_cannot_view_other_profiles_or_export(): void
    {
        $user = $this->cmsUser();
        $other = User::factory()->create();

        $this->actingAs($user)->get("/admin/users/{$other->id}")->assertForbidden();
        $this->get('/admin/users/export/csv')->assertForbidden();
        $this->get('/admin/users/export/pdf')->assertForbidden();
    }

    public function test_any_admin_can_update_their_own_profile_and_photo(): void
    {
        Storage::fake('public');
        $user = $this->cmsUser();

        $this->actingAs($user)->get('/admin/profile')->assertOk()->assertSee('My profile')->assertSee('name="current_password"', false);

        $this->from('/admin/profile')->put('/admin/profile', [
            'name' => 'Renamed Self', 'email' => $user->email, 'location' => 'Mbale',
            'photo' => UploadedFile::fake()->image('self.png', 200, 200),
        ])->assertRedirect('/admin/profile')->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed Self', $user->name);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_changing_own_password_requires_the_current_password(): void
    {
        $user = $this->cmsUser();
        $base = ['name' => $user->name, 'email' => $user->email, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];

        $this->actingAs($user)->put('/admin/profile', $base)->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', $base + ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', $base + ['current_password' => 'OldPassword123!'])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_own_profile_cannot_change_roles(): void
    {
        $user = $this->cmsUser();

        $this->actingAs($user)->put('/admin/profile', ['name' => $user->name, 'email' => $user->email, 'role' => 'super_admin'])
            ->assertSessionHasNoErrors();
        $this->assertFalse($user->organisationRoles()->where('role', 'super_admin')->exists());
    }

    public function test_csv_export_lists_filtered_users_and_neutralises_formulas(): void
    {
        $admin = $this->superAdmin();
        $diocese = $this->diocese();
        User::factory()->create(['name' => '=HYPERLINK("x")', 'diocese_id' => $diocese->id, 'phone' => '+256 772 123456']);
        User::factory()->create(['name' => 'Elsewhere Person']);

        $response = $this->actingAs($admin)->get('/admin/users/export/csv?diocese_id='.$diocese->id)->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();

        $this->assertStringContainsString('ID,Name,Email,Phone,Location,Diocese,Roles,Joined', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('+256 772 123456', $csv);
        $this->assertStringContainsString('Kigezi Diocese', $csv);
        $this->assertStringNotContainsString('Elsewhere Person', $csv);
    }

    public function test_pdf_export_downloads_a_pdf(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get('/admin/users/export/pdf')->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_row_actions_are_icon_buttons_with_hover_titles(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/users')->assertOk()
            ->assertSee('title="View profile"', false)
            ->assertSee('title="Edit user"', false)
            ->assertSee('title="Remove user"', false)
            ->assertDontSee('</i> Edit</button>', false)
            ->assertDontSee('</i> Remove</button>', false);
    }

    public function test_top_bar_links_to_my_profile(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/users')->assertSee(route('admin.profile.show'), false);
    }
}
