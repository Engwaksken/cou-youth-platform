<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_uses_full_available_width_on_large_screens(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('body>.container{width:100%;max-width:none;flex:1}', false)
            ->assertSee('.site-header>.nav{width:100%;max-width:none}', false)
            ->assertSee('padding-inline:var(--page-gutter)', false)
            ->assertDontSee('max-width:1180px;margin:0 auto;padding:44px', false)
            ->assertDontSee('max-width:1180px;margin:auto;padding:18px', false);
    }

    public function test_layout_uses_consistent_responsive_page_gutters(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee(':root{--page-gutter:24px}', false)
            ->assertSee('@media(min-width:1200px){:root{--page-gutter:32px}}', false)
            ->assertSee('@media(max-width:640px){:root{--page-gutter:16px}}', false)
            ->assertDontSee('padding-right:84px', false)
            ->assertDontSee('padding-inline:clamp(', false);
    }

    public function test_links_remain_without_underlines_on_hover_and_focus(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('a,a:hover,a:focus,a:active,a:visited{text-decoration:none!important}', false)
            ->assertSee('.platform-footer a:focus-visible{outline:3px solid #ffbf47', false)
            ->assertSee('.platform-footer a:hover{color:#fff}', false);
    }

    public function test_public_footer_has_four_columns_and_current_copyright(): void
    {
        $response = $this->get(route('home'))->assertOk();
        preg_match('/<footer class="platform-footer">.*?<\/footer>/s', $response->getContent(), $matches);

        $this->assertNotEmpty($matches);
        $this->assertSame(4, substr_count($matches[0], 'class="platform-footer-column"'));
        $this->assertStringContainsString('&copy; '.date('Y').' Church of Uganda. All rights reserved.', $matches[0]);
        $this->assertStringContainsString(route('register'), $matches[0]);
        $response->assertSee('grid-template-columns:repeat(2,minmax(0,1fr))', false)
            ->assertSee('grid-template-columns:minmax(0,1fr)', false);
    }

    public function test_youth_portal_includes_shared_footer_and_account_links(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('youth.dashboard'))
            ->assertOk()
            ->assertSee('class="portal-content"', false)
            ->assertSee('class="platform-footer"', false)
            ->assertSee('My Profile')
            ->assertSee('All rights reserved.');
    }

    public function test_admin_portal_includes_shared_footer(): void
    {
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('class="platform-footer"', false)
            ->assertSee('All rights reserved.');
    }

    public function test_footer_escapes_branding_and_shows_configured_support_email(): void
    {
        $this->blade('<x-platform-footer :brand="$brand" />', [
            'brand' => [
                'name' => '<script>alert(1)</script>',
                'support_email' => 'support@example.org',
            ],
        ])->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>', false)
            ->assertSee('mailto:support@example.org', false);
    }
}
