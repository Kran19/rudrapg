<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminService
{
    public function getDashboardKpis(): array
    {
        return [
            'total_branches' => Branch::count(),
            'total_students' => Student::where('status', 'APPROVED')->count(),
            'total_rooms' => Room::count(),
            'total_beds' => Bed::count(),
            'occupied_beds' => Bed::where('status', 'OCCUPIED')->count(),
            'available_beds' => Bed::where('status', 'AVAILABLE')->count(),
            'total_revenue' => Payment::where('status', 'VERIFIED')->sum('amount'),
            'occupancy_rate' => Bed::count() > 0 ? round((Bed::where('status', 'OCCUPIED')->count() / Bed::count()) * 100, 1) : 0,
        ];
    }

    public function createBranch(array $data): Branch
    {
        $code = 'PG-'.strtoupper(substr($data['name'], 0, 3)).'-'.sprintf('%02d', Branch::count() + 1);
        $qrHash = 'hash_'.strtolower(substr($data['name'], 0, 3)).'_'.rand(1000, 9999);

        return Branch::create(array_merge($data, [
            'code' => $code,
            'qr_code_hash' => $qrHash,
            'status' => 'ACTIVE',
        ]));
    }

    public function createSubAdmin(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password'] ?? 'secret123'),
                'role' => 'SUB_ADMIN',
                'status' => 'ACTIVE',
            ]);

            if (! empty($data['branch_ids'])) {
                $user->branches()->sync($data['branch_ids']);
            }

            return $user;
        });
    }

    public function updateSubAdmin(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::where('role', 'SUB_ADMIN')->findOrFail($id);
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? $user->phone,
            ]);

            if (isset($data['branch_ids'])) {
                $user->branches()->sync($data['branch_ids']);
            }

            return $user;
        });
    }

    public function toggleSubAdminStatus(int $id): User
    {
        $user = User::where('role', 'SUB_ADMIN')->findOrFail($id);
        $user->status = ($user->status === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $user->save();

        return $user;
    }

    public function resetSubAdminPassword(int $id, string $newPassword): User
    {
        $user = User::where('role', 'SUB_ADMIN')->findOrFail($id);
        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    public function assignSubAdmin(int $userId, int $branchId): void
    {
        $user = User::findOrFail($userId);
        $user->branches()->syncWithoutDetaching([$branchId]);
    }

    public function getAuditLogs()
    {
        return AuditLog::with('user')->latest()->paginate(20);
    }
}
