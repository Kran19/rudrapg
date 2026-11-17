<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\User;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Student;
use App\Models\RoomAllocation;
use App\Models\Payment;
use App\Models\Complaint;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Identify canonical Odhav branch
        $allOdhavBranches = Branch::withTrashed()
            ->where(function($q) {
                $q->where('name', 'LIKE', '%Odhav%')
                  ->orWhere('code', 'LIKE', '%ODH%')
                  ->orWhere('code', 'Odhav');
            })
            ->get();

        $canonicalOdhav = $allOdhavBranches->firstWhere('status', 'ACTIVE') ?? $allOdhavBranches->first();

        if (!$canonicalOdhav) {
            $canonicalOdhav = Branch::create([
                'name' => 'Rudra p g(Odhav)',
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
            if ($canonicalOdhav->trashed()) {
                $canonicalOdhav->restore();
            }
            $canonicalOdhav->name = 'Rudra p g(Odhav)';
            $canonicalOdhav->status = 'ACTIVE';
            $canonicalOdhav->save();
        }

        // If duplicate Odhav branches exist, move everything to the canonical one
        foreach ($allOdhavBranches as $b) {
            if ($b->id !== $canonicalOdhav->id) {
                Room::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
                Student::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
                RoomAllocation::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
                Payment::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
                Complaint::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);

                // Pivot cleanup: delete duplicate before updating
                $existingUserIds = DB::table('sub_admin_branch')->where('branch_id', $canonicalOdhav->id)->pluck('user_id')->toArray();
                DB::table('sub_admin_branch')->where('branch_id', $b->id)->whereIn('user_id', $existingUserIds)->delete();
                DB::table('sub_admin_branch')->where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);

                $b->delete(); // Soft delete duplicate
            }
        }

        // 2. Ensure rooms 401 to 428 and their beds are assigned to canonical Odhav branch
        $odhavRoomNumbers = array_map('strval', range(401, 428));
        Room::whereIn('room_number', $odhavRoomNumbers)->update([
            'branch_id' => $canonicalOdhav->id,
            'floor_number' => 4,
            'max_beds' => 3,
            'sharing_type' => '3-Sharing AC',
            'is_ac' => true,
        ]);

        // Standardize bed codes for rooms 401 to 428
        $odhavRooms = Room::where('branch_id', $canonicalOdhav->id)->whereIn('room_number', $odhavRoomNumbers)->get();
        foreach ($odhavRooms as $room) {
            $beds = Bed::where('room_id', $room->id)->orderBy('id')->get();
            $letters = ['A', 'B', 'C'];
            for ($i = 0; $i < 3; $i++) {
                $code = 'Bed ' . $room->room_number . '-' . $letters[$i];
                if (isset($beds[$i])) {
                    $beds[$i]->update(['bed_code' => $code]);
                } else {
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_code' => $code,
                        'monthly_rent' => 6500.00,
                        'security_deposit' => 10000.00,
                        'status' => 'AVAILABLE',
                    ]);
                }
            }
        }

        // 3. Connect Odhav Sub-Admins to canonical Odhav Branch in pivot table
        $odhavSubAdmins = User::where('role', 'SUB_ADMIN')
            ->where(function ($q) {
                $q->where('email', 'LIKE', '%odhav%')
                  ->orWhere('name', 'LIKE', '%odhav%');
            })
            ->get();

        foreach ($odhavSubAdmins as $adminUser) {
            DB::table('sub_admin_branch')->where('user_id', $adminUser->id)->delete();
            DB::table('sub_admin_branch')->insert([
                'user_id' => $adminUser->id,
                'branch_id' => $canonicalOdhav->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Ensure any Odhav subadmin user has status ACTIVE
        User::where('email', 'odhav@rudrapg.com')->update(['status' => 'ACTIVE']);

        // 5. Clean up ghost/dummy OCCUPIED beds that have NO student and NO active allocation
        $realStudentBedIds = Student::whereNotNull('bed_id')->pluck('bed_id')->toArray();
        $realAllocBedIds = RoomAllocation::where('status', 'ACTIVE')->pluck('bed_id')->toArray();
        $allActiveOccupiedIds = array_unique(array_merge($realStudentBedIds, $realAllocBedIds));

        Bed::whereNotIn('id', $allActiveOccupiedIds)
            ->where('status', 'OCCUPIED')
            ->update(['status' => 'AVAILABLE']);
    }

    public function down(): void
    {
        // Safe no-op
    }
};
