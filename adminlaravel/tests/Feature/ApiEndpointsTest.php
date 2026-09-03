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

    public function test_complaint_resolution_remarks_flow(): void
    {
        // 1. Log in student
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'rahul.sharma@gmail.com',
            'password' => 'password',
        ]);
        $studentToken = $loginRes->json('data.token');

        // 2. Student creates a complaint
        $complaintRes = $this->withHeader('Authorization', 'Bearer '.$studentToken)
            ->postJson('/api/v1/student/complaint', [
                'category' => 'PLUMBING',
                'subject' => 'Leaky Geyser',
                'description' => 'Geyser is leaking water from the bottom valve.',
            ]);
        $complaintRes->assertStatus(201);
        $complaintId = $complaintRes->json('data.id');

        // 3. Log in Sub Admin
        $subAdminLoginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'subadmin.naroda@rudrapg.com',
            'password' => 'password',
        ]);
        $subAdminToken = $subAdminLoginRes->json('data.token');

        // 4. Sub Admin updates complaint to IN_PROGRESS with resolution remarks
        // Simulate a session update by hitting the web controller update method
        $subAdminUser = \App\Models\User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $this->actingAs($subAdminUser)
            ->postJson("/sub-admin/complaints/{$complaintId}/update", [
                'status' => 'IN_PROGRESS',
                'priority' => 'MEDIUM',
                'resolution_remarks' => 'Technician has been scheduled for today 4 PM.',
            ])
            ->assertStatus(200);

        // Reset auth to student
        $studentUser = \App\Models\User::where('email', 'rahul.sharma@gmail.com')->first();
        $this->actingAs($studentUser, 'sanctum');

        // 5. Student fetches complaints list and verifies remarks are present
        $studentComplaintsRes = $this->getJson('/api/v1/student/complaints');
        
        $studentComplaintsRes->assertStatus(200)
            ->assertJsonFragment([
                'id' => $complaintId,
                'status' => 'IN_PROGRESS',
                'resolution_remarks' => 'Technician has been scheduled for today 4 PM.',
            ]);
    }
}
