<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_a_structured_response(): void
    {
        $response = $this->getJson('/api/v1/health');

        $this->assertContains($response->status(), [200, 503]);
        $response->assertJsonStructure([
            'status', 'service', 'environment', 'time',
            'checks' => ['database', 'storage', 'app_key', 'debug_disabled'],
        ]);
    }
}
