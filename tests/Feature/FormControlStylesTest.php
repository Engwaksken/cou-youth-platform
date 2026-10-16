<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormControlStylesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_auth_pages_include_standard_control_sizes(): void
    {
        foreach (['home', 'login', 'register', 'password.request'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('--control-height:2.5rem', false)
                ->assertSee('--control-height:2.75rem', false)
                ->assertSee('body textarea{box-sizing:border-box;min-height:104px', false)
                ->assertSee(':not([type="checkbox"]):not([type="radio"])', false);
        }
    }

    public function test_primary_button_hover_has_a_public_theme_color_fallback(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Create Youth Account')
            ->assertSee('body .btn.btn-primary:hover,body .btn.btn-primary:focus-visible', false)
            ->assertSee('background:var(--secondary,var(--primary-2,#204f78))', false)
            ->assertSee('border-color:var(--secondary,var(--primary-2,#204f78));color:#fff', false);
    }
}
