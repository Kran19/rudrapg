<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure Odhav Branch exists
        $branch = Branch::withTrashed()->where('name', 'LIKE', '%Odhav%')->orWhere('code', 'PG-ODH-01')->first();
        if (!$branch) {
            $branch = Branch::create([
                'name' => 'Odhav Branch',
                'code' => 'PG-ODH-01',
                'address' => 'Opposite Odhav Ring Road, Odhav, Ahmedabad',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '382415',
                'phone' => '9876543220',
                'email' => 'odhav@rudrapg.com',
                'manager_name' => 'Ramesh Patel',
                'manager_phone' => '9876543220',
                'electricity_unit_rate' => 10.00,
                'qr_code_hash' => 'hash_odhav_88291024',
                'status' => 'ACTIVE',
            ]);
        } else {
            if ($branch->trashed()) {
                $branch->restore();
            }
            $branch->update(['status' => 'ACTIVE']);
        }

        // 2. Add Rooms 401 to 428 with 3 beds per room (Floor 4)
        for ($roomNum = 401; $roomNum <= 428; $roomNum++) {
            $room = Room::withTrashed()
                ->where('branch_id', $branch->id)
                ->where('room_number', (string) $roomNum)
                ->first();

            if (!$room) {
                $room = Room::create([
                    'branch_id' => $branch->id,
                    'floor_number' => 4,
                    'room_number' => (string) $roomNum,
                    'sharing_type' => '3-Sharing AC',
                    'max_beds' => 3,
                    'is_ac' => true,
                    'status' => 'AVAILABLE',
                ]);
            } else {
                if ($room->trashed()) {
                    $room->restore();
                }
                $room->update([
                    'floor_number' => 4,
                    'sharing_type' => '3-Sharing AC',
                    'max_beds' => 3,
                    'is_ac' => true,
                ]);
            }

            // Create/Ensure 3 Beds: A, B, C
            $letters = ['A', 'B', 'C'];
            foreach ($letters as $char) {
                $bedCode = 'Bed ' . $roomNum . '-' . $char;
                $existingBed = Bed::withTrashed()
                    ->where('room_id', $room->id)
                    ->where(function($q) use ($bedCode, $char) {
                        $q->where('bed_code', $bedCode)
                          ->orWhere('bed_code', 'Bed ' . $char);
                    })
                    ->first();

                if (!$existingBed) {
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_code' => $bedCode,
                        'monthly_rent' => 6000.00,
                        'security_deposit' => 10000.00,
                        'status' => 'AVAILABLE',
                    ]);
                } else {
                    if ($existingBed->trashed()) {
                        $existingBed->restore();
                    }
                    if ($existingBed->status === 'AVAILABLE') {
                        $existingBed->update([
                            'monthly_rent' => 6000.00,
                            'security_deposit' => 10000.00,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Safe no-op down to preserve room data
    }
};

