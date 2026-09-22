<?php

namespace Tests\Browser;

use Tests\TestCase;

/**
 * Sanity checks for touched views: public layout nav/a11y/chat toggles,
 * auth flash fade, welcome + public slides/cards from controller vars,
 * Auth logo partial. User-visible behavior only.
 */
class TouchedViewsSanityTest extends TestCase
{
    private function view(string $path): string
    {
        return file_get_contents(base_path($path));
    }

    public function test_layout_has_no_bare_body_reference_error(): void
    {
        $html = $this->view('resources/views/public/layout.blade.php');
        // Inline script must declare body (const body=document.body) — a bare
        // `body` identifier with no declaration would throw ReferenceError.
        $this->assertMatchesRegularExpression(
            '/(const|let|var)\s+body\s*=\s*document\.body/',
            $html,
            'layout JS must declare `body` (const body=document.body)'
        );
        // Accessible toggles a user can operate.
        $this->assertStringContainsString('id="navToggle"', $html);
        $this->assertStringContainsString('aria-controls="primaryNav"', $html);
        $this->assertStringContainsString('id="accessToggle"', $html);
        $this->assertStringContainsString('id="chatToggle"', $html);
        $this->assertStringContainsString('href="#main-content"', $html);
    }

    public function test_auth_flash_fade_selector_present(): void
    {
        $html = $this->view('resources/views/livewire/auth-ui.blade.php');
        $this->assertStringContainsString('data-auth-flash', $html);
        $this->assertStringContainsString('[data-auth-flash].is-fading', $html);
    }

    public function test_slides_and_cards_come_from_controller_vars(): void
    {
        foreach ([
            'resources/views/welcome.blade.php',
            'resources/views/public/about.blade.php',
            'resources/views/public/news.blade.php',
            'resources/views/public/events.blade.php',
            'resources/views/public/courses.blade.php',
            'resources/views/public/churches.blade.php',
            'resources/views/public/donate.blade.php',
        ] as $view) {
            $html = $this->view($view);
            $this->assertStringContainsString('$slides', $html, "slides var missing in {$view}");
            $this->assertStringContainsString('$cards', $html, "cards var missing in {$view}");
            $this->assertStringContainsString('slide-card', $html, "slide card missing in {$view}");
        }
    }

    public function test_auth_logo_partial_constrained(): void
    {
        $html = $this->view('resources/views/components/auth-brand.blade.php');
        $this->assertStringContainsString('alt=', $html);
        $this->assertMatchesRegularExpression('/max-height:\s*56px/i', $html);
    }
}
