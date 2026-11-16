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
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
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

    public function test_login_strictly_requires_correct_password_without_fallback(): void
    {
        $user = User::create([
            'name' => 'Custom Password Resident',
            'email' => 'custom.pw@example.com',
            'phone' => '9111122222',
            'password' => Hash::make('MyCustomPass@99'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        // Wrong password attempt (even default password123) must be rejected
        $wrongRes = $this->postJson('/api/v1/auth/login', [
            'phone' => '9111122222',
            'password' => 'password123',
        ]);
        $wrongRes->assertStatus(422);

        // Correct password must succeed
        $correctRes = $this->postJson('/api/v1/auth/login', [
            'phone' => '9111122222',
            'password' => 'MyCustomPass@99',
        ]);
        $correctRes->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_student_can_request_forgot_password(): void
    {
        $user = User::create([
            'name' => 'Forgot Password Resident',
            'email' => 'forgot.pw@example.com',
            'phone' => '9333344444',
            'password' => Hash::make('OldSecret@123'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password-request', [
            'phone' => '9333344444',
            'notes' => 'I cannot remember my login password',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'status' => 'PENDING',
        ]);
    }

    public function test_sub_admin_can_reset_password_for_branch_student(): void
    {
        $branch = Branch::where('code', 'PG-NRD-01')->first();
        $subAdmin = User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $subAdmin->branches()->sync([$branch->id]);

        $studentUser = User::create([
            'name' => 'Need Reset Resident',
            'email' => 'need.reset@example.com',
            'phone' => '9444455555',
            'password' => Hash::make('StalePass#11'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'branch_id' => $branch->id,
            'app_reference' => 'REG-RESET-01',
            'full_name' => 'Need Reset Resident',
            'phone' => '9444455555',
            'email' => 'need.reset@example.com',
            'aadhaar_number' => '123456789012',
            'parent_name' => 'Parent Name',
            'parent_phone' => '9444455550',
            'emergency_contact' => '9444455550',
            'current_address' => 'Test Address',
            'status' => 'APPROVED',
        ]);

        $resetReq = \App\Models\PasswordResetRequest::create([
            'user_id' => $studentUser->id,
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'phone' => $studentUser->phone,
            'status' => 'PENDING',
        ]);

        // SubAdmin resets password
        $res = $this->actingAs($subAdmin)->postJson("/sub-admin/password-requests/{$resetReq->id}/reset", [
            'new_password' => 'NewGenerated@2026',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Verify request marked resolved
        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $resetReq->id,
            'status' => 'RESOLVED',
            'resolved_by' => $subAdmin->id,
        ]);

        // Verify student can now login with new password
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'phone' => '9444455555',
            'password' => 'NewGenerated@2026',
        ]);
        $loginRes->assertStatus(200);
    }

    public function test_sub_admin_cross_branch_password_reset_rejected_with_403(): void
    {
        $branch1 = Branch::where('code', 'PG-NRD-01')->first();
        $subAdmin = User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $subAdmin->branches()->sync([$branch1->id]);

        $branch2 = Branch::create([
            'code' => 'PG-OTHER-02',
            'name' => 'Other Branch',
            'address' => 'Other Address',
            'city' => 'Ahmedabad',
            'phone' => '+91 79228 11111',
            'manager_name' => 'Other Manager',
            'manager_phone' => '+91 98888 11111',
            'qr_code_hash' => 'hash_other_02',
            'status' => 'ACTIVE',
        ]);

        $otherUser = User::create([
            'name' => 'Other Branch Resident',
            'email' => 'other.res@example.com',
            'phone' => '9555566666',
            'password' => Hash::make('Secret#99'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        $otherStudent = Student::create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch2->id,
            'app_reference' => 'REG-OTHER-01',
            'full_name' => 'Other Branch Resident',
            'phone' => '9555566666',
            'email' => 'other.res@example.com',
            'aadhaar_number' => '999988887777',
            'parent_name' => 'Parent Name',
            'parent_phone' => '9555566660',
            'emergency_contact' => '9555566660',
            'current_address' => 'Test Address',
            'status' => 'APPROVED',
        ]);

        $resetReq = \App\Models\PasswordResetRequest::create([
            'user_id' => $otherUser->id,
            'student_id' => $otherStudent->id,
            'branch_id' => $branch2->id,
            'phone' => $otherUser->phone,
            'status' => 'PENDING',
        ]);

        // SubAdmin of branch 1 attempts to reset password of student in branch 2
        $res = $this->actingAs($subAdmin)->postJson("/sub-admin/password-requests/{$resetReq->id}/reset", [
            'new_password' => 'HackedPass@123',
        ]);

        // Must be rejected with 403 Forbidden!
        $res->assertStatus(403);
    }

    public function test_sub_admin_direct_reset_returns_json_with_success_and_new_password(): void
    {
        $branch = Branch::first();
        $subAdmin = User::where('email', 'subadmin.naroda@rudrapg.com')->first();
        $subAdmin->branches()->sync([$branch->id]);

        $user = User::create([
            'name' => 'Direct Reset Resident',
            'email' => 'direct.res@example.com',
            'phone' => '9444455555',
            'password' => Hash::make('OldPass@123'),
            'role' => 'STUDENT',
            'status' => 'ACTIVE',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'app_reference' => 'REG-DIR-01',
            'full_name' => 'Direct Reset Resident',
            'phone' => '9444455555',
            'email' => 'direct.res@example.com',
            'aadhaar_number' => '112233445566',
            'parent_name' => 'Parent Name',
            'parent_phone' => '9444455550',
            'emergency_contact' => '9444455550',
            'current_address' => 'Test Address',
            'status' => 'APPROVED',
        ]);

        // JSON Request
        $res = $this->actingAs($subAdmin)->postJson("/sub-admin/students/{$student->id}/reset-password", [
            'new_password' => 'NewRudra@2026',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('new_password', 'NewRudra@2026');

        $this->assertTrue(Hash::check('NewRudra@2026', $user->fresh()->password));
    }
}
