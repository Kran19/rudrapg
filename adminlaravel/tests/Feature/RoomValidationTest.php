<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $branch1;
    protected $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        
        $this->superAdmin = User::where('role', 'SUPER_ADMIN')->first();
        $this->branch1 = Branch::first();
        
        // Create a second branch for testing cross-branch unique validation
        $this->branch2 = Branch::create([
            'name' => 'Branch 2',
            'code' => 'PG-BR2-02',
            'address' => 'Branch 2 Address',
            'city' => 'Ahmedabad',
            'phone' => '+91 99999 99999',
            'email' => 'branch2@rudrapg.com',
            'manager_name' => 'Manager 2',
            'manager_phone' => '+91 99999 99999',
            'electricity_unit_rate' => 12.00,
            'qr_code_hash' => 'hash_branch_2',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_can_create_unique_room(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/rooms-master', [
            'branch_id' => $this->branch1->id,
            'room_number' => '999',
            'floor_number' => 1,
            'sharing_type' => 'Single Sharing',
            'max_beds' => 1,
            'is_ac' => 1,
            'rent' => 8000,
            'deposit' => 15000,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rooms', [
            'branch_id' => $this->branch1->id,
            'room_number' => '999',
        ]);
    }

    public function test_cannot_create_duplicate_room_at_same_branch(): void
    {
        // First create room 999 at branch 1
        Room::create([
            'branch_id' => $this->branch1->id,
            'room_number' => '999',
            'floor_number' => 1,
            'sharing_type' => 'Single Sharing',
            'max_beds' => 1,
            'is_ac' => true,
            'status' => 'AVAILABLE',
        ]);

        // Attempt to create room 999 again at branch 1
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/rooms-master', [
            'branch_id' => $this->branch1->id,
            'room_number' => '999',
            'floor_number' => 1,
            'sharing_type' => 'Single Sharing',
            'max_beds' => 1,
            'is_ac' => 1,
            'rent' => 8000,
            'deposit' => 15000,
        ]);

        $response->assertSessionHasErrors(['room_number']);
    }

    public function test_can_create_same_room_number_at_different_branch(): void
    {
        // First create room 999 at branch 1
        Room::create([
            'branch_id' => $this->branch1->id,
            'room_number' => '999',
            'floor_number' => 1,
            'sharing_type' => 'Single Sharing',
            'max_beds' => 1,
            'is_ac' => true,
            'status' => 'AVAILABLE',
        ]);

        // Attempt to create room 999 at branch 2
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/rooms-master', [
            'branch_id' => $this->branch2->id,
            'room_number' => '999',
            'floor_number' => 1,
            'sharing_type' => 'Single Sharing',
            'max_beds' => 1,
            'is_ac' => 1,
            'rent' => 8000,
            'deposit' => 15000,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rooms', [
            'branch_id' => $this->branch2->id,
            'room_number' => '999',
        ]);
    }
}
