<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReleaseHealthTest extends TestCase
{
    public function test_health_endpoint_is_available(): void
    {
        $this->getJson('/api/v1/health')->assertSuccessful();
    }

    public function test_release_endpoint_returns_version_metadata(): void
    {
        $this->getJson('/api/v1/release')
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['version', 'build', 'channel', 'minimum_mobile_version']]);
    }
}
