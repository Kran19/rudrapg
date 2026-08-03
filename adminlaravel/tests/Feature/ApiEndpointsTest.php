<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_can_login_and_fetch_profile(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rahul.sharma@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $token = $response->json('data.token');

        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/student/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.full_name', 'Rahul Sharma')
            ->assertJsonPath('data.app_reference', 'REG-2026-8812');
    }

    public function test_sub_admin_can_access_pending_verifications_and_room_matrix(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'subadmin.naroda@rudrapg.com',
            'password' => 'password',
        ]);

        $loginRes->assertStatus(200);
        $token = $loginRes->json('data.token');

        $matrixRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/sub-admin/room-matrix');

        $matrixRes->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_super_admin_can_access_dashboard_kpis(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@rudrapg.com',
            'password' => 'password',
        ]);

        $loginRes->assertStatus(200);
        $token = $loginRes->json('data.token');

        $dashRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/super-admin/dashboard');

        $dashRes->assertStatus(200)
            ->assertJsonPath('data.total_branches', 1)
            ->assertJsonPath('data.total_rooms', 40)
            ->assertJsonPath('data.total_beds', 80);
    }
}
