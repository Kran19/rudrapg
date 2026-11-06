<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_sub_admin_cannot_approve_other_branch_record(): void
    {
        // 1. Create a second branch
        $branch2 = Branch::create([
            'code' => 'PG-NV-02',
            'name' => 'Navrangpura Branch',
            'address' => 'Navrangpura, Ahmedabad',
            'city' => 'Ahmedabad',
            'phone' => '+91 79228 99999',
            'manager_name' => 'Branch 2 Manager',
            'manager_phone' => '+91 98888 22222',
            'qr_code_hash' => 'hash_b2_9999',
            'status' => 'ACTIVE',
        ]);

        // 2. Create student & registration request in Branch 2
        $user2 = User::create([
            'name' => 'Branch 2 Resident',
            'email' => 'b2.resident@example.com',
            'phone' => '9888877777',
            'password' => Hash::make('password123'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        $student2 = Student::create([
            'user_id' => $user2->id,
            'branch_id' => $branch2->id,
            'app_reference' => 'REG-2026-9999',
            'full_name' => 'Branch 2 Resident',
            'phone' => '9888877777',
            'email' => 'b2.resident@example.com',
            'aadhaar_number' => '112233445566',
            'parent_name' => 'Parent Name',
            'parent_phone' => '9888877776',
            'emergency_contact' => '9888877776',
            'current_address' => 'Sample Address',
            'status' => 'PENDING_APPROVAL',
        ]);

        $regReq2 = RegistrationRequest::create([
            'branch_id' => $branch2->id,
            'student_id' => $student2->id,
            'app_reference' => 'REG-2026-9999',
            'status' => 'PENDING',
        ]);

        // 3. Login as Sub Admin of Naroda Branch only
        $narodaSubAdmin = User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $narodaBranch = Branch::where('code', 'PG-NRD-01')->first();
        $narodaSubAdmin->branches()->sync([$narodaBranch->id]);

        $this->actingAs($narodaSubAdmin);

        // 4. Attempt to approve KYC of Branch 2 student
        $response = $this->postJson("/sub-admin/verifications/{$regReq2->id}/approve-kyc");
        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');

        // 5. Attempt to approve admission of Branch 2 student
        $response2 = $this->postJson("/sub-admin/verifications/{$regReq2->id}/approve");
        $response2->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    public function test_student_can_delete_account_and_creates_ticket(): void
    {
        $studentUser = User::where('email', 'rahul.sharma@gmail.com')->first();
        $student = Student::where('user_id', $studentUser->id)->first();

        // Create an active room allocation
        $room = Room::first();
        $bed = Bed::where('room_id', $room->id)->first();
        RoomAllocation::create([
            'student_id' => $student->id,
            'branch_id' => $student->branch_id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'start_date' => now()->toDateString(),
            'monthly_rent' => 6500.00,
            'security_deposit' => 10000.00,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($studentUser, 'sanctum')->deleteJson('/api/v1/student/account');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('complaints', [
            'student_id' => $student->id,
            'subject' => 'Resident Account Deletion & Exit Request',
            'status' => 'OPEN',
        ]);
    }

    public function test_bed_auto_allocation_is_branch_scoped(): void
    {
        $narodaBranch = Branch::where('code', 'PG-NRD-01')->first();
        
        // Mark all beds in Naroda Branch as OCCUPIED
        Bed::whereHas('room', fn($q) => $q->where('branch_id', $narodaBranch->id))->update(['status' => 'OCCUPIED']);

        // Create Branch 2 with available bed
        $branch2 = Branch::create([
            'code' => 'PG-NV-03',
            'name' => 'Branch 3',
            'address' => 'Address 3',
            'city' => 'Ahmedabad',
            'phone' => '+91 79228 11111',
            'manager_name' => 'Branch 3 Manager',
            'manager_phone' => '+91 98888 33333',
            'qr_code_hash' => 'hash_b3_3333',
            'status' => 'ACTIVE',
        ]);
        $room2 = Room::create([
            'branch_id' => $branch2->id,
            'floor_number' => 1,
            'room_number' => '301',
            'sharing_type' => '2 Sharing AC',
            'max_beds' => 1,
            'status' => 'AVAILABLE',
        ]);
        $bed2 = Bed::create([
            'room_id' => $room2->id,
            'bed_code' => 'Bed 301A',
            'monthly_rent' => 7000.00,
            'security_deposit' => 10000.00,
            'status' => 'AVAILABLE',
        ]);

        // Register student at Naroda Branch
        $userNaroda = User::create([
            'name' => 'Naroda Applicant',
            'email' => 'naroda.app@example.com',
            'phone' => '9777766666',
            'password' => Hash::make('password123'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);
        $studentNaroda = Student::create([
            'user_id' => $userNaroda->id,
            'branch_id' => $narodaBranch->id,
            'app_reference' => 'REG-NRD-7777',
            'full_name' => 'Naroda Applicant',
            'phone' => '9777766666',
            'email' => 'naroda.app@example.com',
            'aadhaar_number' => '998877665544',
            'parent_name' => 'Parent Name',
            'parent_phone' => '9777766665',
            'emergency_contact' => '9777766665',
            'current_address' => 'Sample Address',
            'kyc_status' => 'APPROVED',
            'status' => 'KYC_APPROVED',
        ]);
        $regReq = RegistrationRequest::create([
            'branch_id' => $narodaBranch->id,
            'student_id' => $studentNaroda->id,
            'app_reference' => 'REG-NRD-7777',
            'status' => 'KYC_APPROVED',
        ]);

        // Sub Admin attempts to auto-assign bed
        $subAdmin = User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $subAdmin->branches()->sync([$narodaBranch->id]);

        $response = $this->actingAs($subAdmin)->postJson("/sub-admin/verifications/{$regReq->id}/approve");

        // Must fail with 422 because Naroda branch has no beds, and NOT steal bed from Branch 3!
        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Confirm Branch 2 bed remains AVAILABLE
        $this->assertDatabaseHas('beds', [
            'id' => $bed2->id,
            'status' => 'AVAILABLE',
        ]);
    }
}
