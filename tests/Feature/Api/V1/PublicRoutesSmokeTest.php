<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class PublicRoutesSmokeTest extends TestCase
{
    public function test_public_api_routes_do_not_return_404(): void
    {
        foreach (['/api/v1/organisation-units', '/api/v1/content', '/api/v1/events', '/api/v1/courses', '/api/v1/media', '/api/v1/church-locator'] as $uri) {
            $this->assertNotSame(404, $this->getJson($uri)->status(), $uri.' unexpectedly returned 404');
        }
    }
}
