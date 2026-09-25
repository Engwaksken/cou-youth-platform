<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class BrandingEndpointTest extends TestCase
{
    public function test_branding_endpoint_is_public_and_returns_platform_identity(): void
    {
        $this->getJson('/api/v1/branding')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.short_name', 'COU Youth Platform')
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'short_name',
                    'tagline',
                    'primary_color',
                    'secondary_color',
                    'support_email',
                    'logo_url',
                ],
            ]);
    }
}
