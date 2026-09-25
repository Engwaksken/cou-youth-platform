<?php

namespace Tests\Browser;

use Tests\TestCase;

/**
 * User-visible polish spot-checks (no implementation-detail assertions
 * beyond what a user can see/do).
 */
class PolishSpotCheckTest extends TestCase
{
    private function view(string $path): string
    {
        return file_get_contents(base_path($path));
    }

    public function test_auth_pages_show_sized_logo_and_eye_toggle(): void
    {
        foreach ([
            'resources/views/Auth/youth-login.blade.php',
            'resources/views/Auth/youth-register.blade.php',
            'resources/views/Auth/admin-login.blade.php',
        ] as $view) {
            $html = $this->view($view);
            // Logo visible with constrained sizing (user sees a reasonably sized logo).
            $this->assertMatchesRegularExpression('/max-height:\s*56px/i', $html, "logo sizing missing in {$view}");
            $this->assertStringContainsString('alt=', $html, "logo alt text missing in {$view}");
            // Eye toggle is an accessible button a user can operate.
            $this->assertStringContainsString('data-pw-toggle', $html, "eye toggle missing in {$view}");
            $this->assertStringContainsString('aria-label="Show password"', $html, "toggle label missing in {$view}");
        }
    }

    public function test_flash_messages_fade_after_five_seconds(): void
    {
        $admin = $this->view('resources/views/admin/layout.blade.php');
        $this->assertStringContainsString('data-auto-dismiss', $admin);
        $this->assertStringContainsString('5000', $admin, '5s flash fade missing in admin layout');

        $auth = $this->view('resources/views/Auth/admin-login.blade.php');
        $this->assertStringContainsString('data-auth-flash', $auth);
        $this->assertStringContainsString('is-fading', $auth);
    }

    public function test_public_banners_and_cards_have_empty_states(): void
    {
        foreach ([
            'resources/views/public/events.blade.php' => 'No upcoming events found.',
            'resources/views/public/courses.blade.php' => 'No published courses found.',
            'resources/views/public/news.blade.php' => 'No published content found.',
            'resources/views/public/churches.blade.php' => 'No church locations found.',
            'resources/views/public/donate.blade.php' => 'No active donation campaigns are available.',
        ] as $view => $emptyText) {
            $html = $this->view($view);
            $this->assertStringContainsString('class="card"', $html, "card missing in {$view}");
            $this->assertStringContainsString($emptyText, $html, "empty state missing in {$view}");
        }
    }

    public function test_removed_sentence_is_absent(): void
    {
        // The stale placeholder sentence removed during polish must not reappear.
        $banned = 'Lorem ipsum dolor sit amet';
        foreach (glob(base_path('resources/views/**/*.blade.php')) as $file) {
            $this->assertStringNotContainsString($banned, file_get_contents($file), "banned sentence in {$file}");
        }
    }
}
