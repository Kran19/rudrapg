<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\RegistrationRequest;
use App\Models\Room;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_can_register_via_qr_code(): void
    {
        $response = $this->postJson('/api/v1/student/register', [
            'branch_code' => 'PG-NRD-01',
            'full_name' => 'Vikram Shah',
            'phone' => '+91 99887 76655',
            'email' => 'vikram.shah@gmail.com',
            'password' => 'secret123',
            'aadhaar_number' => 'XXXX-XXXX-1122',
            'pan_number' => 'ABCDE9988F',
            'parent_name' => 'Kishore Shah',
            'parent_phone' => '+91 98250 88776',
            'current_address' => '404, Science City Road, Ahmedabad',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', 'Vikram Shah')
            ->assertJsonPath('data.status', 'PENDING_APPROVAL');

        $this->assertDatabaseHas('registration_requests', [
            'status' => 'PENDING',
        ]);
    }

    public function test_sub_admin_can_approve_registration_and_allocate_bed(): void
    {
        // Register new student
        $regRes = $this->postJson('/api/v1/student/register', [
            'branch_code' => 'PG-NRD-01',
            'full_name' => 'Anil Kapoor',
            'phone' => '+91 91122 33445',
            'email' => 'anil.k@gmail.com',
            'password' => 'secret123',
            'aadhaar_number' => 'XXXX-XXXX-3344',
            'parent_name' => 'Suraj Kapoor',
            'parent_phone' => '+91 98250 11998',
            'current_address' => '12, CG Road, Ahmedabad',
        ]);

        $reqId = RegistrationRequest::where('app_reference', $regRes->json('data.app_reference'))->first()->id;

        // Sub Admin Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'subadmin.naroda@rudrapg.com',
            'password' => 'password',
        ]);

        $token = $loginRes->json('data.token');

        $room = Room::first();
        $bed = Bed::where('status', 'AVAILABLE')->first();

        // Approve Registration
        $approveRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sub-admin/registrations/'.$reqId.'/approve', [
                'room_id' => $room->id,
                'bed_id' => $bed->id,
                'joining_date' => '2026-08-05',
            ]);

        $approveRes->assertStatus(200)
            ->assertJsonPath('data.status', 'APPROVED');

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'OCCUPIED',
        ]);

        $this->assertDatabaseHas('room_allocations', [
            'bed_id' => $bed->id,
            'status' => 'ACTIVE',
        ]);
    }
}
