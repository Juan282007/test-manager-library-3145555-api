<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_returns_ok_status_for_the_public_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $response->assertExactJson(['status' => 'ok']);
    }
}
