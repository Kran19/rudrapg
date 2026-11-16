<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\User;
use App\Models\Room;
use App\Models\Bed;

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
            $canonicalOdhav->status = 'ACTIVE';
            $canonicalOdhav->save();
        }

        // If duplicate Odhav branches exist, move any rooms/beds to the canonical one
        foreach ($allOdhavBranches as $b) {
            if ($b->id !== $canonicalOdhav->id) {
                Room::where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
                // Update pivot
                DB::table('sub_admin_branch')->where('branch_id', $b->id)->update(['branch_id' => $canonicalOdhav->id]);
            }
        }

        // 2. Ensure rooms 401 to 428 and their beds are assigned to canonical Odhav branch
        Room::whereBetween('room_number', [401, 428])->update(['branch_id' => $canonicalOdhav->id]);

        // 3. Connect all Odhav Sub-Admins to canonical Odhav Branch in pivot table
        $odhavSubAdmins = User::where('role', 'SUB_ADMIN')
            ->where(function ($q) {
                $q->where('email', 'LIKE', '%odhav%')
                  ->orWhere('name', 'LIKE', '%odhav%');
            })
            ->get();

        foreach ($odhavSubAdmins as $adminUser) {
            // Delete old pivot if any to prevent duplicate primary keys
            DB::table('sub_admin_branch')->where('user_id', $adminUser->id)->delete();
            DB::table('sub_admin_branch')->insert([
                'user_id' => $adminUser->id,
                'branch_id' => $canonicalOdhav->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Safe no-op
    }
};
