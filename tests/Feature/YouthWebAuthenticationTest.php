<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class YouthWebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_login_is_youth_login_and_admin_login_is_separate(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign In')
            ->assertSee('Create youth account')
            ->assertDontSee('For Church of Uganda Youth Platform youth members.')
            ->assertDontSee('CMS Login');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('For authorised Church of Uganda Youth Platform administrators.')
            ->assertDontSee('Create youth account')
            ->assertDontSee('CMS Login');
    }

    public function test_youth_can_register_and_is_signed_in(): void
    {
        $response = $this->post('/signup', [
            'name' => 'Youth Member',
            'email' => 'youth@example.com',
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'school_institution' => 'Youth Institute',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ]);

        $response->assertRedirect(route('youth.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'youth@example.com']);
        $this->assertDatabaseHas('youth_profiles', ['age_category' => 'youth']);
    }

    public function test_youth_login_uses_standard_web_session(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => Hash::make('StrongPass123'),
        ]);

        $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'StrongPass123',
        ])->assertRedirect(route('youth.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_public_sections_are_separate_pages_for_guests(): void
    {
        foreach (['/news', '/events', '/courses', '/churches', '/donate', '/about'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_authenticated_donate_route_enters_youth_portal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/donate')
            ->assertRedirect(route('youth.donations'));
    }
}
