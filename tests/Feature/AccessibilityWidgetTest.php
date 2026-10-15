<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_have_labelled_accessibility_controls(): void
    {
        foreach (['home', 'public.donate', 'login'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('role="dialog" aria-labelledby="accessTitle"', false)
                ->assertSee('Text &amp; reading', false)
                ->assertSee('Vision &amp; navigation', false)
                ->assertSee('Read aloud')
                ->assertSee('id="accessTextSize" aria-live="polite"', false)
                ->assertSee('id="focusMode" type="checkbox"', false)
                ->assertSee('max-height:calc(100dvh - 16px)', false);
        }
    }

    public function test_donation_message_uses_white_text_on_colored_card(): void
    {
        $this->get(route('public.donate'))->assertOk()
            ->assertSee('.donate-hero .giving-message p {', false)
            ->assertSee('Every contribution helps strengthen youth discipleship,');
    }
}
