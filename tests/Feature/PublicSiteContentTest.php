<?php

namespace Tests\Feature;

use App\Models\PageCard;
use App\Models\PageSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteContentTest extends TestCase
{
    use RefreshDatabase;

    private function seedHomeContent(): array
    {
        // NOTE: no factories exist for PageSlide/PageCard (only UserFactory),
        // so rows are created directly via mass assignment per model $fillable.
        $activeSlide = PageSlide::create([
            'page' => 'home', 'title' => 'Active Slide', 'subtitle' => 'Hello',
            'media_type' => 'image', 'sort_order' => 1, 'is_active' => true,
        ]);
        PageSlide::create([
            'page' => 'home', 'title' => 'Inactive Slide',
            'media_type' => 'image', 'sort_order' => 0, 'is_active' => false,
        ]);
        PageSlide::create([
            'page' => 'news', 'title' => 'Other Page Slide',
            'media_type' => 'image', 'sort_order' => 0, 'is_active' => true,
        ]);
        PageCard::create([
            'page' => 'home', 'title' => 'Active Card', 'body' => 'Body',
            'sort_order' => 1, 'is_active' => true,
        ]);
        PageCard::create([
            'page' => 'home', 'title' => 'Inactive Card',
            'sort_order' => 0, 'is_active' => false,
        ]);

        return [$activeSlide];
    }

    public function test_home_passes_active_slides_and_cards_to_view(): void
    {
        $this->seedHomeContent();
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertViewHas('slides', function ($slides) {
            return $slides->contains('title', 'Active Slide')
                && ! $slides->contains('title', 'Inactive Slide')
                && ! $slides->contains('title', 'Other Page Slide');
        });
        $response->assertViewHas('cards', function ($cards) {
            return $cards->contains('title', 'Active Card')
                && ! $cards->contains('title', 'Inactive Card');
        });
    }

    public function test_home_renders_slide_and_card_titles(): void
    {
        $this->seedHomeContent();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Active Slide')
            ->assertSee('Active Card')
            ->assertDontSee('Inactive Slide')
            ->assertDontSee('Other Page Slide');
    }

    public function test_about_page_passes_own_slides_and_cards(): void
    {
        PageSlide::create(['page' => 'about', 'title' => 'About Slide', 'media_type' => 'image', 'sort_order' => 0, 'is_active' => true]);
        PageCard::create(['page' => 'about', 'title' => 'About Card', 'body' => 'x', 'sort_order' => 0, 'is_active' => true]);
        PageSlide::create(['page' => 'home', 'title' => 'Home Only Slide', 'media_type' => 'image', 'sort_order' => 0, 'is_active' => true]);

        $response = $this->get(route('public.about'));
        $response->assertOk();
        $response->assertViewHas('slides', fn ($s) => $s->contains('title', 'About Slide') && ! $s->contains('title', 'Home Only Slide'));
        $response->assertViewHas('cards', fn ($c) => $c->contains('title', 'About Card'));
    }

    public function test_branding_default_renders_without_settings(): void
    {
        // Empty site_settings table: layout must fall back to sane default.
        $this->get(route('home'))->assertOk()
            ->assertSee('Church of Uganda Youth Platform');
    }

    public function test_stale_placeholder_sentence_absent_from_home(): void
    {
        $this->seedHomeContent();
        $this->get(route('home'))->assertOk()
            ->assertDontSee('Coming soon', false)
            ->assertDontSee('Lorem ipsum', false)
            ->assertDontSee('Under construction', false);
    }

    public function test_admin_content_routes_still_registered_and_guarded(): void
    {
        foreach (['admin.page-content.index', 'admin.settings.index'] as $name) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), "Route {$name} missing");
        }
        // Guest (unauthenticated) must not get 404/500 on admin routes.
        $this->get(route('admin.page-content.index'))->assertRedirect(route('login'));
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
    }
}
