<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GymFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_member_can_log_in_and_receive_a_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'profile',
                ],
            ]);
    }

    public function test_authenticated_member_can_fetch_mvp_resources(): void
    {
        $this->seed();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ])->json();

        $headers = [
            'Authorization' => 'Bearer '.$loginResponse['token'],
        ];

        $this->withHeaders($headers)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('header.appName', 'GymFlow');

        $this->withHeaders($headers)
            ->getJson('/api/v1/workouts')
            ->assertOk()
            ->assertJsonStructure([
                'filters',
                'workouts',
            ]);
    }
}
