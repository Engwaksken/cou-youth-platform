<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\DonationCampaign;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_payment_gateway_cannot_be_used_for_a_donation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $gateway = PaymentGateway::create([
            'name' => 'Disabled Gateway',
            'slug' => 'disabled-gateway',
            'provider' => 'test',
            'currency' => 'UGX',
            'credentials' => [],
            'settings' => [],
            'is_test_mode' => true,
            'is_enabled' => false,
            'sort_order' => 1,
        ]);

        $response = $this->postJson('/api/v1/donations', [
            'payment_gateway_id' => $gateway->id,
            'amount' => 1000,
            'currency' => 'UGX',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_gateway_id');
    }

    public function test_gateway_currency_must_match_donation_currency(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $gateway = PaymentGateway::create([
            'name' => 'UGX Gateway',
            'slug' => 'ugx-gateway',
            'provider' => 'test',
            'currency' => 'UGX',
            'credentials' => [],
            'settings' => [],
            'is_test_mode' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        $response = $this->postJson('/api/v1/donations', [
            'payment_gateway_id' => $gateway->id,
            'amount' => 1000,
            'currency' => 'USD',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('currency');
    }

    public function test_closed_campaign_cannot_receive_donations(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $gateway = PaymentGateway::create([
            'name' => 'UGX Gateway',
            'slug' => 'ugx-gateway',
            'provider' => 'test',
            'currency' => 'UGX',
            'credentials' => [],
            'settings' => [],
            'is_test_mode' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        $campaign = DonationCampaign::create([
            'title' => 'Closed Campaign',
            'slug' => 'closed-campaign',
            'description' => 'Closed campaign test.',
            'target_amount' => 100000,
            'currency' => 'UGX',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
            'status' => 'published',
            'allow_anonymous' => true,
        ]);

        $response = $this->postJson('/api/v1/donations', [
            'campaign_id' => $campaign->id,
            'payment_gateway_id' => $gateway->id,
            'amount' => 1000,
            'currency' => 'UGX',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('campaign_id');
    }
}
