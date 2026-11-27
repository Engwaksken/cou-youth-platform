<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Services\Branding\AppearanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true]);

        return $admin;
    }

    /** @param array<string, mixed> $overrides */
    private function appearance(array $overrides = []): array
    {
        return array_merge([
            'primary_color' => '#0B6E4F',
            'secondary_color' => '#08415C',
            'accent_color' => '#F2A541',
            'background_color' => '#F7F4EF',
            'sidebar_color' => '#14213D',
            'heading_font' => 'Poppins',
            'body_font' => 'Nunito',
            'theme_style' => 'soft',
        ], $overrides);
    }

    public function test_admin_sees_the_appearance_tab_with_current_values(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.settings.index'))->assertOk()
            ->assertSee('id="settings-appearance"', false)
            ->assertSee('name="heading_font"', false)
            ->assertSee('name="theme_style" value="clean"', false)
            ->assertSee('name="logo"', false)
            ->assertSee('name="favicon"', false);
    }

    public function test_saved_appearance_applies_to_public_portal_and_admin(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->appearance())->assertSessionHasNoErrors();

        $this->get(route('home'))->assertOk()
            ->assertSee('--primary:#0B6E4F', false)
            ->assertSee('--sun:#F2A541', false)
            ->assertSee('--paper:#F7F4EF', false)
            ->assertSee("--font-display:'Poppins'", false)
            ->assertSee("--font-body:'Nunito'", false)
            ->assertSee('family=Poppins', false)
            ->assertSee('--r-card:20px;--r-btn:999px;--tilt:.5', false);

        // A dark sidebar gets white text in the youth portal and in admin.
        $this->actingAs(User::factory()->create())->get(route('youth.dashboard'))->assertOk()
            ->assertSee('--side-bg:#14213D;--side-ink:#FFFFFF', false);
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()
            ->assertSee(':root{--side-bg:#14213D;--side-ink:#FFFFFF}', false)
            ->assertSee("font-family:'Nunito'", false);
    }

    public function test_light_sidebar_gets_dark_text_and_default_sidebar_can_be_restored(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->appearance(['sidebar_color' => '#FFF8E1']));
        $this->assertSame('#FFF8E1', app(AppearanceService::class)->data()['sidebar_color']);
        $this->assertSame('#1D1530', app(AppearanceService::class)->data()['sidebar_text']);

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->appearance(['sidebar_default' => '1']));
        $this->assertNull(app(AppearanceService::class)->data()['sidebar_color']);
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertDontSee('--side-bg:', false);
    }

    public function test_reset_restores_the_default_look(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->appearance());
        $this->actingAs($admin)->put(route('admin.settings.update'), ['reset_appearance' => '1'])
            ->assertSessionHas('success', 'Settings saved. Appearance is back to the default look.');

        $look = app(AppearanceService::class)->data();
        $this->assertSame('Bricolage Grotesque', $look['heading_font']);
        $this->assertSame('playful', $look['theme_style']);
        $this->assertSame(strtoupper((string) config('branding.primary_color')), strtoupper($look['primary_color']));
        $this->assertNull($look['sidebar_color']);
    }

    public function test_invalid_values_are_rejected_and_nothing_is_injected(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.update'), $this->appearance([
                'primary_color' => 'red;}</style><script>alert(1)</script>',
                'heading_font' => 'Comic Sans; } body{display:none',
                'theme_style' => 'neon',
            ]))
            ->assertSessionHasErrors(['primary_color', 'heading_font', 'theme_style']);

        $this->assertNull(SiteSetting::get('primary_color'));
        $this->get(route('home'))->assertOk()->assertDontSee('alert(1)', false);
    }

    public function test_uploaded_logo_is_used_instead_of_other_images_in_storage(): void
    {
        Storage::fake('public');
        // An older file sitting in the branding folder must not win over the uploaded logo.
        Storage::disk('public')->put('branding/aaa-old-favicon.png', 'x');

        $this->actingAs($this->superAdmin())->put(route('admin.settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png', 120, 40),
        ])->assertSessionHasNoErrors();

        $logo = SiteSetting::get('logo');
        $this->get(route('home'))->assertOk()->assertSee(Storage::disk('public')->url($logo), false);
    }
}
