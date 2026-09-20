<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_returns_sanctum_token_and_token_can_access_me(): void
    {
        $user = User::factory()->create([
            'email' => 'youth@example.com',
            'password' => Hash::make('StrongPass123'),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'youth@example.com',
            'password' => 'StrongPass123',
            'device_name' => 'Flutter test device',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'youth@example.com')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $token = (string) $login->json('token');
        $this->assertNotSame('', $token);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_invalid_mobile_credentials_do_not_issue_token(): void
    {
        User::factory()->create([
            'email' => 'youth@example.com',
            'password' => Hash::make('StrongPass123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'youth@example.com',
            'password' => 'WrongPassword123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
