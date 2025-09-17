<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'service',
                'version',
            ])
            ->assertJson([
                'status' => 'ok',
                'service' => 'user-microservice',
            ]);
    }

    public function test_ready_check_endpoint(): void
    {
        $response = $this->getJson('/api/ready');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['status'],
                    'cache' => ['status'],
                ],
            ]);
    }

    public function test_metrics_endpoint(): void
    {
        $response = $this->get('/api/metrics');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/plain; version=0.0.4; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContains('app_users_total', $content);
        $this->assertStringContains('db_connection_latency_ms', $content);
        $this->assertStringContains('cache_latency_ms', $content);
    }
}