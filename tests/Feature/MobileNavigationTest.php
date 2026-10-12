<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_mobile_toggle_contains_branding_and_controls_sidebar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo.png', 'test logo');
        Cache::forget('platform_branding_logo_url');
        SiteSetting::set('system_name', 'Youth Ministry');
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users')->assertOk();
        $html = $response->getContent();
        preg_match('/<button[^>]*data-mobile-nav-toggle.*?<\/button>/s', $html, $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringContainsString('aria-controls="adminSidebar"', $matches[0]);
        $this->assertStringContainsString('aria-expanded="false"', $matches[0]);
        $this->assertStringContainsString('branding/logo.png', $matches[0]);
        $this->assertStringContainsString('Youth Ministry', $matches[0]);
        $response->assertSee('id="adminSidebar"', false)->assertSee('data-mobile-nav-backdrop', false);
    }

    public function test_youth_mobile_toggle_has_fallback_brand_and_shared_navigation(): void
    {
        Storage::fake('public');
        Cache::forget('platform_branding_logo_url');
        SiteSetting::set('system_name', 'Youth Portal');

        $response = $this->actingAs(User::factory()->create())->get(route('youth.dashboard'))->assertOk();
        preg_match('/<button[^>]*data-mobile-nav-toggle.*?<\/button>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringContainsString('aria-controls="portalSidebar"', $matches[0]);
        $this->assertStringContainsString('fa-church', $matches[0]);
        $this->assertStringContainsString('Youth Portal', $matches[0]);
        $response->assertSee('data-mobile-nav-backdrop', false)->assertSee('body.mobile-nav-open', false);
    }
}
