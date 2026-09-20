<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianConsentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teen_registration_requires_confirmed_guardian_consent(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Teen Member',
            'email' => 'teen@example.test',
            'password' => 'StrongPass9',
            'password_confirmation' => 'StrongPass9',
            'date_of_birth' => now()->subYears(15)->toDateString(),
            'guardian_name' => 'Parent Member',
            'guardian_relationship' => 'Parent',
            'guardian_phone' => '+256700000000',
            'guardian_confirmed' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guardian_confirmed']);

        $this->assertDatabaseMissing('users', [
            'email' => 'teen@example.test',
        ]);
    }

    public function test_confirmed_teen_registration_creates_pending_guardian_consent(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Teen Member',
            'email' => 'teen@example.test',
            'password' => 'StrongPass9',
            'password_confirmation' => 'StrongPass9',
            'date_of_birth' => now()->subYears(15)->toDateString(),
            'guardian_name' => 'Parent Member',
            'guardian_relationship' => 'Parent',
            'guardian_phone' => '+256700000000',
            'guardian_email' => 'parent@example.test',
            'guardian_confirmed' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.category', 'teen');

        $userId = (int) $response->json('data.user.id');

        $this->assertDatabaseHas('guardian_consents', [
            'user_id' => $userId,
            'guardian_name' => 'Parent Member',
            'relationship' => 'Parent',
            'guardian_phone' => '+256700000000',
            'status' => 'pending',
        ]);

        $this->assertNotNull(
            \DB::table('guardian_consents')
                ->where('user_id', $userId)
                ->value('consented_at')
        );
    }

    public function test_adult_registration_does_not_require_guardian_details(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Youth Member',
            'email' => 'youth@example.test',
            'password' => 'StrongPass9',
            'password_confirmation' => 'StrongPass9',
            'date_of_birth' => now()->subYears(21)->toDateString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.category', 'youth');

        $this->assertDatabaseCount('guardian_consents', 0);
    }
}
