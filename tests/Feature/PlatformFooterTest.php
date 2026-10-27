<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_caps_and_centres_content_on_large_screens(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('body>.container,.site-header>.nav,.platform-footer-grid,.platform-footer-bottom-inner{width:100%;max-width:var(--content-max);margin-inline:auto}', false)
            ->assertSee('padding-inline:var(--page-gutter)', false)
            ->assertDontSee('max-width:1180px', false)
            ->assertDontSee('max-width:none', false);
    }

    public function test_layout_scales_for_phones_wide_monitors_and_tvs(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee(':root{--base-font:16px;--page-gutter:24px;--content-max:80rem;', false)
            ->assertSee('@media(max-width:640px){:root{--page-gutter:16px}}', false)
            ->assertSee('@media(min-width:1200px){:root{--page-gutter:32px}}', false)
            ->assertSee('@media(min-width:1920px){:root{--base-font:17px;', false)
            ->assertSee('@media(min-width:2400px){:root{--base-font:20px;', false)
            ->assertSee('@media(min-width:3200px){:root{--base-font:26px}}', false)
            ->assertSee('html{font-size:calc(var(--base-font) * var(--text-scale,1))', false)
            ->assertDontSee('padding-right:84px', false);
    }

    public function test_portal_chrome_uses_shared_scale_tokens(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('youth.dashboard'))
            ->assertOk()
            ->assertSee('.top{height:var(--top-h);', false)
            ->assertSee('@media(min-width:801px){.wrap{grid-template-columns:var(--side-w) minmax(0,1fr)}', false)
            ->assertDontSee('calc(100vh - 68px)', false);
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

    public function test_youth_portal_does_not_render_public_footer(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('youth.dashboard'))
            ->assertOk()
            ->assertSee('class="portal-content"', false)
            ->assertSee(route('youth.profile'), false)
            ->assertDontSee('<footer class="platform-footer">', false)
            ->assertDontSee('All rights reserved.');
    }

    public function test_admin_portal_does_not_render_public_footer(): void
    {
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertDontSee('<footer class="platform-footer">', false)
            ->assertDontSee('All rights reserved.');
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
