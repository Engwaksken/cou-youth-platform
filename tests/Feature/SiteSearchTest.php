<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Event;
use App\Models\LifeGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_search_is_on_public_and_portal_pages(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('class="header-search"', false)
            ->assertSee('action="'.route('public.search').'"', false);

        $this->actingAs(User::factory()->create())
            ->get(route('youth.dashboard'))->assertOk()
            ->assertSee('class="header-search"', false);
    }

    public function test_search_finds_published_items_across_kinds_and_hides_unpublished_ones(): void
    {
        Event::create(['title' => 'Kampala Worship Night', 'starts_at' => now()->addDays(3), 'status' => 'published']);
        Event::create(['title' => 'Draft Worship Retreat', 'starts_at' => now()->addDays(5), 'status' => 'draft']);
        Course::forceCreate(['title' => 'Worship and the Psalms', 'description' => 'Six lessons.', 'is_published' => true]);
        Course::forceCreate(['title' => 'Hidden Worship Course', 'description' => 'Not out yet.', 'is_published' => false]);
        LifeGroup::create(['name' => 'Worship Team Fellowship', 'is_active' => true]);
        LifeGroup::create(['name' => 'Closed Worship Circle', 'is_active' => false]);

        $this->get(route('public.search', ['q' => 'worship']))->assertOk()
            ->assertSee('Kampala Worship Night')
            ->assertSee('Worship and the Psalms')
            ->assertSee('Worship Team Fellowship')
            ->assertSeeText('3 matches across the site.')
            ->assertDontSee('Draft Worship Retreat')
            ->assertDontSee('Hidden Worship Course')
            ->assertDontSee('Closed Worship Circle');
    }

    public function test_like_wildcards_in_the_query_are_treated_literally(): void
    {
        Event::create(['title' => 'Youth Camp', 'starts_at' => now()->addDay(), 'status' => 'published']);

        $this->get(route('public.search', ['q' => '%%']))->assertOk()
            ->assertSeeText('Nothing matched')
            ->assertDontSee('Youth Camp');
    }

    public function test_empty_and_too_short_queries_get_friendly_prompts(): void
    {
        $this->get(route('public.search'))->assertOk()->assertSeeText('What are you looking for?');
        $this->get(route('public.search', ['q' => 'a']))->assertOk()->assertSeeText('Type a little more');
    }

    public function test_signed_in_members_see_results_inside_their_portal(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('public.search', ['q' => 'camp']))->assertOk()
            ->assertSee('id="portalSidebar"', false);
    }
}
