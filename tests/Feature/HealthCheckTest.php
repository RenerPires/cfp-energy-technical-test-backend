<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_path_health_check(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertJson([
            "service"=>"user-management-service",
            "status"=>"ok"
        ]);
    }
}
