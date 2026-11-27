<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PageCard;
use App\Models\PageText;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTextEditingTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithRole(string $role): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => $role, 'is_active' => true]);

        return $user;
    }

    public function test_every_public_page_shows_its_default_text(): void
    {
        $this->get('/')->assertOk()->assertSee('Faith that fits')->assertSee('Connect. Grow. Serve.');
        $this->get(route('public.about'))->assertOk()->assertSee('About COU Youth');
        $this->get(route('public.events'))->assertOk()->assertSee('No upcoming events just now');
        $this->get(route('public.courses'))->assertOk()->assertSee('Courses to help you');
        $this->get(route('public.life-groups'))->assertOk()->assertSee('Life Groups: find your');
        $this->get(route('public.churches'))->assertOk()->assertSee('Find a church');
        $this->get(route('public.news'))->assertOk()->assertSee('Stories &amp;', false);
        $this->get(route('public.donate'))->assertOk()->assertSee('Support Youth Ministry');
    }

    public function test_super_admin_sees_every_field_with_its_current_text(): void
    {
        $this->actingAs($this->adminWithRole('super_admin'))
            ->get(route('admin.page-content.index', ['page' => 'about']))
            ->assertOk()
            ->assertSee('Page text')
            ->assertSee('name="texts[feature_6_body]"', false)
            ->assertSee('About COU Youth');
    }

    public function test_super_admin_can_change_and_reset_page_text(): void
    {
        $admin = $this->adminWithRole('super_admin');

        $this->actingAs($admin)
            ->put(route('admin.page-content.texts.update', 'home'), ['texts' => [
                'hero_title' => 'Walk with Jesus',
                'hero_intro' => "Line one\nLine two",
                'events_title' => 'Coming up', // unchanged: no override stored
            ]])
            ->assertRedirect(route('admin.page-content.index', ['page' => 'home']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('page_texts', ['page' => 'home', 'key' => 'hero_title', 'value' => 'Walk with Jesus', 'updated_by' => $admin->id]);
        $this->assertDatabaseMissing('page_texts', ['page' => 'home', 'key' => 'events_title']);

        $this->get('/')->assertSee('Walk with Jesus')->assertSee("Line one<br>\nLine two", false)->assertDontSee('Faith that fits');

        $this->actingAs($admin)
            ->put(route('admin.page-content.texts.update', 'home'), [
                'texts' => ['hero_title' => 'Walk with Jesus', 'hero_intro' => ''],
                'reset' => ['hero_title' => '1'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, PageText::query()->where('page', 'home')->count());
        $this->get('/')->assertSee('Faith that fits');
    }

    public function test_page_text_is_escaped(): void
    {
        PageText::create(['page' => 'about', 'key' => 'hero_title', 'value' => '<script>alert(1)</script>']);

        $this->get(route('public.about'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_content_manager_can_manage_cards_but_not_page_text(): void
    {
        $manager = $this->adminWithRole('content_manager');

        $this->actingAs($manager)
            ->get(route('admin.page-content.index', ['page' => 'home']))
            ->assertOk()
            ->assertDontSee('name="texts[hero_title]"', false);

        $this->actingAs($manager)
            ->put(route('admin.page-content.texts.update', 'home'), ['texts' => ['hero_title' => 'Hacked']])
            ->assertForbidden();

        $this->assertDatabaseCount('page_texts', 0);

        $card = PageCard::create(['page' => 'home', 'title' => 'Camp', 'is_active' => true]);
        $this->actingAs($manager)
            ->put(route('admin.page-content.cards.update', $card), ['page' => 'home', 'title' => 'Youth Camp', 'is_active' => '1'])
            ->assertRedirect();
        $this->assertSame('Youth Camp', $card->refresh()->title);
    }

    public function test_unknown_page_returns_not_found(): void
    {
        $this->actingAs($this->adminWithRole('super_admin'))
            ->put(route('admin.page-content.texts.update', 'nope'), ['texts' => []])
            ->assertNotFound();
    }
}
