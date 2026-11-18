<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Payment;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\RoomAllocation;
use App\Models\StudentDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $currentMonthRevenue = (float) Payment::whereIn('status', ['PAID', 'VERIFIED'])
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $lastMonthRevenue = (float) Payment::whereIn('status', ['PAID', 'VERIFIED'])
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('amount');

        $revenueChange = null;
        if ($lastMonthRevenue > 0) {
            $revenueChange = round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1);
        }

        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'OCCUPIED')->count();
        $availableBeds = Bed::where('status', 'AVAILABLE')->count();

        $metrics = [
            'total_revenue' => '₹'.number_format($currentMonthRevenue),
            'revenue_change' => $revenueChange,
            'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1).'%' : '0%',
            'active_branches' => Branch::where('status', 'ACTIVE')->count(),
            'pending_approvals' => RegistrationRequest::where('status', 'PENDING')->count(),
            'total_branches' => Branch::count(),
            'total_students' => Student::where('status', 'APPROVED')->count(),
            'total_rooms' => Room::count(),
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'monthly_collection' => '₹'.number_format($currentMonthRevenue),
            'pending_payments' => Payment::where('status', 'PENDING')->count(),
            'active_complaints' => Complaint::whereIn('status', ['PENDING', 'IN_PROGRESS'])->count(),
        ];

        // Real Monthly Revenue Analytics per Active Branch (Last 6 Months)
        $chartMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $chartMonths[] = now()->subMonths($i);
        }
        $revenueMonths = array_map(fn($d) => $d->format('M Y'), $chartMonths);

        $palette = ['#0F172A', '#2563EB', '#14B8A6', '#F59E0B', '#8B5CF6', '#EC4899', '#10B981'];
        $branchRevenueDatasets = [];

        $activeBranches = Branch::where('status', 'ACTIVE')->get();
        foreach ($activeBranches as $idx => $b) {
            $monthlyTotals = [];
            foreach ($chartMonths as $d) {
                $monthlyTotals[] = (float) Payment::where('branch_id', $b->id)
                    ->whereIn('status', ['PAID', 'VERIFIED'])
                    ->whereMonth('payment_date', $d->month)
                    ->whereYear('payment_date', $d->year)
                    ->sum('amount');
            }

            $branchRevenueDatasets[] = [
                'label' => $b->name,
                'data' => $monthlyTotals,
                'backgroundColor' => $palette[$idx % count($palette)],
                'borderRadius' => 6,
            ];
        }

        $recentRegistrations = Student::with('branch')->latest()->take(5)->get();
        $recentPayments = Payment::with(['student', 'branch'])->latest()->take(5)->get();
        $branches = Branch::withCount(['rooms', 'beds'])->get();

        $recentActivities = AuditLog::with('user')->latest()->take(5)->get()->map(function ($log) {
            return [
                'timestamp' => $log->created_at ? $log->created_at->diffForHumans() : 'Just now',
                'user' => $log->user ? $log->user->name : 'System Auto',
                'action' => $log->action,
                'details' => $log->module ? 'Module: '.$log->module : 'System Audit Log',
            ];
        })->toArray();

        return view('super_admin.dashboard', compact(
            'metrics',
            'recentRegistrations',
            'recentPayments',
            'branches',
            'recentActivities',
            'revenueMonths',
            'branchRevenueDatasets'
        ));
    }

    public function branches()
    {
        $branches = Branch::withCount(['rooms', 'beds'])->get();

        return view('super_admin.branches', compact('branches'));
    }

    public function storeBranch(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:branches,code'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'phone' => ['required', 'string', 'digits:10'],
            'email' => ['required', 'email'],
            'manager_name' => ['required', 'string'],
            'manager_phone' => ['required', 'string', 'digits:10'],
            'electricity_unit_rate' => ['required', 'numeric', 'min:0'],
        ], [
            'code.unique' => 'This Branch Code already exists. Please enter a different one.'
        ]);

        $validated['qr_code_hash'] = 'hash_'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $validated['code']));
        $validated['status'] = 'ACTIVE';

        $branch = Branch::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created PG Branch: '.$branch->name,
            'module' => 'BRANCH',
            'record_id' => $branch->id,
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'PG Branch created successfully.',
                'data' => $branch,
            ]);
        }

        return redirect()->back()->with('success', 'PG Branch created successfully.');
    }

    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'room_number' => ['required', 'string'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'sharing_type' => ['required', 'string'],
            'max_beds' => ['required', 'integer', 'min:1', 'max:20'],
            'is_ac' => ['required', 'boolean'],
            'rent' => ['required', 'numeric', 'min:0'],
            'deposit' => ['required', 'numeric', 'min:0'],
        ]);

        // Check if decreasing capacity would drop below occupied/reserved beds
        $occupiedBedsCount = Bed::where('room_id', $room->id)
            ->whereIn('status', ['OCCUPIED', 'RESERVED'])
            ->count();

        if ($validated['max_beds'] < $occupiedBedsCount) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot reduce room capacity to {$validated['max_beds']}. There are currently {$occupiedBedsCount} occupied/reserved beds in this room.",
            ], 422);
        }

        DB::transaction(function () use ($room, $validated) {
            $room->update([
                'branch_id' => $validated['branch_id'],
                'room_number' => $validated['room_number'],
                'floor_number' => $validated['floor_number'],
                'sharing_type' => $validated['sharing_type'],
                'max_beds' => $validated['max_beds'],
                'is_ac' => $validated['is_ac'],
            ]);

            // Existing beds in this room
            $existingBeds = Bed::where('room_id', $room->id)->orderBy('id')->get();
            $currentBedCount = $existingBeds->count();
            $targetBedCount = (int) $validated['max_beds'];

            // If increasing capacity: create new beds
            if ($targetBedCount > $currentBedCount) {
                for ($b = $currentBedCount + 1; $b <= $targetBedCount; $b++) {
                    $suffix = chr(64 + $b);
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_code' => 'Bed ' . $validated['room_number'] . '-' . $suffix,
                        'monthly_rent' => $validated['rent'],
                        'security_deposit' => $validated['deposit'],
                        'status' => 'AVAILABLE',
                    ]);
                }
            } 
            // If decreasing capacity: remove unoccupied available beds from the end
            elseif ($targetBedCount < $currentBedCount) {
                $bedsToRemoveCount = $currentBedCount - $targetBedCount;
                $removableBeds = Bed::where('room_id', $room->id)
                    ->where('status', 'AVAILABLE')
                    ->orderByDesc('id')
                    ->take($bedsToRemoveCount)
                    ->get();

                foreach ($removableBeds as $rb) {
                    $rb->delete();
                }
            }

            // Update all available beds' rent and deposit
            Bed::where('room_id', $room->id)
                ->where('status', 'AVAILABLE')
                ->update([
                    'monthly_rent' => $validated['rent'],
                    'security_deposit' => $validated['deposit'],
                ]);
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Room '.$room->room_number.' (Capacity: '.$validated['max_beds'].' beds)',
            'module' => 'ROOMS',
            'record_id' => $room->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Room and occupancy updated successfully.',
        ]);
    }

    public function destroyRoom($id)
    {
        try {
            $room = Room::with('beds')->findOrFail($id);

            // Guard against deleting rooms with occupied beds
            $hasOccupied = $room->beds->contains('status', 'OCCUPIED');
            if ($hasOccupied) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot delete Room {$room->room_number} because it has occupied beds with active residents. Please vacate or reallocate residents first.",
                ], 422);
            }

            DB::transaction(function () use ($room) {
                $room->beds()->delete();
                $room->delete();
            });

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Deleted Room ' . $room->room_number,
                'module' => 'ROOMS',
                'record_id' => (string) $room->id,
                'ip_address' => request()->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Room {$room->room_number} deleted successfully.",
                'room_id' => $room->id,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDestroyRooms(Request $request)
    {
        try {
            $validated = $request->validate([
                'room_ids' => ['required', 'array', 'min:1'],
                'room_ids.*' => ['exists:rooms,id'],
            ]);

            $rooms = Room::with('beds')->whereIn('id', $validated['room_ids'])->get();
            $deletedIds = [];
            $skippedRooms = [];

            DB::transaction(function () use ($rooms, &$deletedIds, &$skippedRooms) {
                foreach ($rooms as $room) {
                    $hasOccupied = $room->beds->contains('status', 'OCCUPIED');
                    if ($hasOccupied) {
                        $skippedRooms[] = $room->room_number;
                        continue;
                    }

                    $room->beds()->delete();
                    $room->delete();
                    $deletedIds[] = $room->id;
                }
            });

            if (count($deletedIds) > 0) {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Bulk deleted ' . count($deletedIds) . ' room(s)',
                    'module' => 'ROOMS',
                    'record_id' => implode(',', $deletedIds),
                    'ip_address' => request()->ip(),
                ]);
            }

            if (count($deletedIds) === 0 && !empty($skippedRooms)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot delete selected room(s) because Room(s) " . implode(', ', $skippedRooms) . " have occupied beds with active residents.",
                ], 422);
            }

            $message = "Successfully deleted " . count($deletedIds) . " room(s).";
            if (!empty($skippedRooms)) {
                $message .= " Note: Room(s) " . implode(', ', $skippedRooms) . " were skipped because they contain occupied beds.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'deleted_ids' => $deletedIds,
                'skipped_rooms' => $skippedRooms,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to bulk delete rooms: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to bulk delete rooms: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'manager_name' => ['required', 'string'],
            'manager_phone' => ['required', 'string'],
            'electricity_unit_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $branch->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated PG Branch: '.$branch->name,
            'module' => 'BRANCH',
            'record_id' => $branch->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PG Branch updated successfully.',
            'data' => $branch,
        ]);
    }

    public function toggleBranchStatus(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $newStatus = $branch->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        
        $branch->update(['status' => $newStatus]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Toggled PG Branch Status: '.$branch->name.' to '.$newStatus,
            'module' => 'BRANCH',
            'record_id' => $branch->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch status updated to ' . $newStatus,
            'status_val' => $newStatus,
        ]);
    }

    public function destroyBranch($id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->subAdmins()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete branch because it has sub-admins assigned to it.',
            ]);
        }

        $branch->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted PG Branch: '.$branch->name,
            'module' => 'BRANCH',
            'record_id' => $branch->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PG Branch deleted successfully.',
        ]);
    }

    public function subAdmins()
    {
        $subAdminsData = User::where('role', 'SUB_ADMIN')->with('branches')->get()->map(function ($user) {
            return [
                'id' => 'SA-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'assigned_branches' => $user->branches->pluck('name')->toArray(),
                'status' => $user->status == 'ACTIVE' ? 'Active' : ($user->status == 'INACTIVE' ? 'Inactive' : ($user->status ?? 'Active')),
                'raw_status' => $user->status ?? 'ACTIVE',
                'branch_ids' => $user->branches->pluck('id')->toArray(),
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : 'N/A',
                'db_id' => $user->id,
            ];
        });

        $allBranches = Branch::all();

        return view('super_admin.sub_admins', ['subAdmins' => $subAdminsData, 'allBranches' => $allBranches]);
    }

    public function storeSubAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'phone' => ['required', 'string', 'digits:10'],
                'password' => ['required', 'string', 'min:6'],
                'branches' => ['required', 'array', 'min:1'],
                'branches.*' => ['exists:branches,id'],
            ], [
                'email.email' => 'Please provide a valid email address.',
                'phone.digits' => 'Mobile number must be exactly 10 digits.',
                'branches.min' => 'Please select at least 1 branch to assign.',
            ]);

            // Check if active user already exists with this email or phone
            $existingActiveUser = User::where(function ($q) use ($validated) {
                $q->where('email', $validated['email'])
                  ->orWhere('phone', $validated['phone']);
            })->first();

            if ($existingActiveUser) {
                if ($existingActiveUser->email === $validated['email']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'A user account with email ' . $validated['email'] . ' already exists.',
                        'errors' => ['email' => ['A user account with this email address already exists.']]
                    ], 422);
                }
                if ($existingActiveUser->phone === $validated['phone']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'A user account with mobile number ' . $validated['phone'] . ' already exists.',
                        'errors' => ['phone' => ['A user account with this mobile number already exists.']]
                    ], 422);
                }
            }

            // Check if soft-deleted user exists with this email/phone to restore
            $trashedUser = User::onlyTrashed()
                ->where(function ($q) use ($validated) {
                    $q->where('email', $validated['email'])
                      ->orWhere('phone', $validated['phone']);
                })
                ->first();

            $user = null;
            DB::transaction(function () use ($validated, $trashedUser, &$user) {
                if ($trashedUser) {
                    $trashedUser->restore();
                    $trashedUser->update([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'password' => $validated['password'],
                        'role' => 'SUB_ADMIN',
                        'status' => 'ACTIVE',
                    ]);
                    $user = $trashedUser;
                } else {
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'password' => $validated['password'],
                        'role' => 'SUB_ADMIN',
                        'status' => 'ACTIVE',
                    ]);
                }

                $user->branches()->sync($validated['branches']);
            });

            if (!$user) {
                throw new \Exception('Failed to create or update user.');
            }

            $user->load('branches');

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Sub Admin: '.$user->email,
                'module' => 'USERS',
                'record_id' => (string) $user->id,
                'ip_address' => $request->ip(),
            ]);

            $responseData = [
                'id' => 'SA-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'assigned_branches' => $user->branches->pluck('name')->toArray(),
                'status' => 'Active',
                'raw_status' => 'ACTIVE',
                'branch_ids' => $user->branches->pluck('id')->toArray(),
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : now()->format('d M Y'),
                'db_id' => $user->id,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Sub Admin account created successfully! Account is now active.',
                'data' => $responseData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create sub admin: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create sub admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSubAdmin(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
                'phone' => ['required', 'string', 'digits:10', 'unique:users,phone,'.$id],
                'password' => ['nullable', 'string', 'min:6'],
                'branches' => ['required', 'array', 'min:1'],
                'branches.*' => ['exists:branches,id'],
            ], [
                'email.unique' => 'A user account with this email address already exists.',
                'phone.unique' => 'A user account with this mobile number already exists.',
                'branches.min' => 'Please select at least 1 branch to assign.',
            ]);

            DB::transaction(function () use ($user, $validated) {
                $user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);

                if (!empty($validated['password'])) {
                    $user->update([
                        'password' => $validated['password'],
                    ]);
                }

                $user->branches()->sync($validated['branches']);
            });

            $user->load('branches');

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Sub Admin: '.$user->email,
                'module' => 'USERS',
                'record_id' => (string) $user->id,
                'ip_address' => $request->ip(),
            ]);

            $responseData = [
                'id' => 'SA-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'assigned_branches' => $user->branches->pluck('name')->toArray(),
                'status' => $user->status == 'ACTIVE' ? 'Active' : ($user->status == 'INACTIVE' ? 'Inactive' : ($user->status ?? 'Active')),
                'raw_status' => $user->status ?? 'ACTIVE',
                'branch_ids' => $user->branches->pluck('id')->toArray(),
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : now()->format('d M Y'),
                'db_id' => $user->id,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Sub Admin account updated successfully.',
                'data' => $responseData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update sub admin: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update sub admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleSubAdminStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        
        $user->update(['status' => $newStatus]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Toggled Sub Admin Status: '.$user->email.' to '.$newStatus,
            'module' => 'USERS',
            'record_id' => $user->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin status updated to ' . $newStatus,
            'status_val' => $newStatus,
        ]);
    }

    public function destroySubAdmin($id)
    {
        $user = User::findOrFail($id);
        
        DB::transaction(function () use ($user) {
            $user->branches()->detach();
            $user->forceDelete();
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted Sub Admin: '.$user->email,
            'module' => 'USERS',
            'record_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin account deleted successfully.',
        ]);
    }

    public function roomsMaster(Request $request)
    {
        try {
            $allBranches = Branch::withCount(['rooms', 'beds'])->get();
            $selectedBranchId = $request->query('branch_id');
            if (!$selectedBranchId && $allBranches->isNotEmpty()) {
                $selectedBranchId = $allBranches->first()->id;
            }

            $roomsData = Room::with([
                'branch',
                'beds.student.documents',
                'beds.activeAllocation.student.documents',
                'students.documents',
            ])
            ->get()
            ->map(function ($room) {
                $totalBeds = $room->beds->count() > 0 ? $room->beds->count() : $room->max_beds;

                $bedsData = $room->beds->map(function ($bed) use ($room) {
                    // Find real student attached to this bed
                    $student = $bed->student ?? $bed->activeAllocation?->student;

                    // Check active allocation records directly
                    if (!$student) {
                        $alloc = RoomAllocation::where('bed_id', $bed->id)->where('status', 'ACTIVE')->latest()->first()
                            ?? RoomAllocation::where('bed_id', $bed->id)->latest()->first();
                        if ($alloc && $alloc->student) {
                            $student = $alloc->student;
                        }
                    }

                    // Check room students if student has matching bed_id or is assigned to this room
                    if (!$student && $room->students && $room->students->isNotEmpty()) {
                        $student = $room->students->firstWhere('bed_id', $bed->id);
                        if (!$student && $room->students->count() === 1 && $room->beds->count() <= 2) {
                            $student = $room->students->first();
                        }
                    }

                    $studentData = null;
                    $isOccupied = ($student !== null);

                    if ($student) {
                        $profileDoc = $student->documents ? $student->documents->firstWhere('doc_type', 'PROFILE_PHOTO') : null;
                        $photoUrl = null;
                        if ($profileDoc && $profileDoc->file_path) {
                            $path = $profileDoc->file_path;
                            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                                $photoUrl = $path;
                            } else {
                                $clean = ltrim(str_replace('storage/', '', $path), '/');
                                $photoUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($clean)
                                    ? asset('storage/' . $clean)
                                    : asset($path);
                            }
                        }

                        $joiningDateFormatted = 'N/A';
                        if ($student->joining_date) {
                            $joiningDateFormatted = is_string($student->joining_date)
                                ? date('d M Y', strtotime($student->joining_date))
                                : $student->joining_date->format('d M Y');
                        }

                        $studentData = [
                            'id' => $student->id,
                            'app_reference' => $student->app_reference ?? 'REG-'.str_pad($student->id, 4, '0', STR_PAD_LEFT),
                            'full_name' => $student->full_name,
                            'phone' => $student->phone,
                            'email' => $student->email ?? 'N/A',
                            'room_number' => (string) $room->room_number,
                            'bed_code' => $bed->bed_code,
                            'branch_name' => $room->branch?->name ?? 'PG Branch',
                            'joining_date' => $joiningDateFormatted,
                            'monthly_rent' => (int) $bed->monthly_rent,
                            'security_deposit' => (int) $bed->security_deposit,
                            'kyc_status' => $student->kyc_status ?? 'VERIFIED',
                            'rent_status' => $student->rent_status ?? 'PAID',
                            'rent_balance_due' => (float)$student->effective_rent_due,
                            'amount_left' => (float)$student->effective_rent_due,
                            'status' => $student->status ?? 'ACTIVE',
                            'parent_name' => $student->parent_name ?? 'N/A',
                            'parent_phone' => $student->parent_phone ?? 'N/A',
                            'emergency_contact' => $student->emergency_contact ?? 'N/A',
                            'current_address' => $student->current_address ?? 'N/A',
                            'aadhaar_number' => $student->aadhaar_number ?? 'N/A',
                            'pan_number' => $student->pan_number ?? 'N/A',
                            'photo_url' => $photoUrl,
                        ];
                    }

                    return [
                        'id' => $bed->id,
                        'bed_code' => $bed->bed_code,
                        'status' => $isOccupied ? 'OCCUPIED' : 'AVAILABLE',
                        'monthly_rent' => (int) $bed->monthly_rent,
                        'security_deposit' => (int) $bed->security_deposit,
                        'is_occupied' => $isOccupied,
                        'student' => $studentData,
                    ];
                });

                $occupiedBeds = $bedsData->where('is_occupied', true)->count();
                $availableBeds = max(0, $totalBeds - $occupiedBeds);

                return [
                    'id' => $room->id,
                    'room_number' => (string) $room->room_number,
                    'floor' => (int) $room->floor_number,
                    'sharing_type' => $room->sharing_type,
                    'is_ac' => (bool) $room->is_ac,
                    'rent' => $room->beds->first() ? (int) $room->beds->first()->monthly_rent : 0,
                    'deposit' => $room->beds->first() ? (int) $room->beds->first()->security_deposit : 0,
                    'total_beds' => $totalBeds,
                    'occupied_beds' => $occupiedBeds,
                    'available_beds' => $availableBeds,
                    'status' => $availableBeds == 0 ? 'Full' : 'Available',
                    'branch_id' => $room->branch_id,
                    'branch_name' => $room->branch?->name ?? 'PG Branch',
                    'max_beds' => (int) $room->max_beds,
                    'beds' => $bedsData->values()->toArray(),
                ];
            })->toArray();

            return view('super_admin.rooms_master', [
                'rooms' => $roomsData,
                'allBranches' => $allBranches,
                'selectedBranchId' => $selectedBranchId ? (int) $selectedBranchId : null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error in roomsMaster: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            // Graceful fallback to basic room data
            $allBranches = Branch::all();
            $roomsData = Room::with('branch', 'beds')->get()->map(function ($r) {
                $total = $r->beds->count() > 0 ? $r->beds->count() : $r->max_beds;
                $occupied = $r->beds->where('status', 'OCCUPIED')->count();
                return [
                    'id' => $r->id,
                    'room_number' => (string) $r->room_number,
                    'floor' => (int) $r->floor_number,
                    'sharing_type' => $r->sharing_type,
                    'is_ac' => (bool) $r->is_ac,
                    'rent' => $r->beds->first() ? (int) $r->beds->first()->monthly_rent : 0,
                    'deposit' => $r->beds->first() ? (int) $r->beds->first()->security_deposit : 0,
                    'total_beds' => $total,
                    'occupied_beds' => $occupied,
                    'available_beds' => max(0, $total - $occupied),
                    'status' => ($total - $occupied) <= 0 ? 'Full' : 'Available',
                    'branch_id' => $r->branch_id,
                    'branch_name' => $r->branch?->name ?? 'PG Branch',
                    'max_beds' => (int) $r->max_beds,
                    'beds' => $r->beds->map(fn($b) => [
                        'id' => $b->id,
                        'bed_code' => $b->bed_code,
                        'status' => $b->status,
                        'monthly_rent' => (int) $b->monthly_rent,
                        'security_deposit' => (int) $b->security_deposit,
                        'is_occupied' => in_array($b->status, ['OCCUPIED', 'RESERVED']),
                        'student' => null,
                    ])->toArray(),
                ];
            })->toArray();

            return view('super_admin.rooms_master', [
                'rooms' => $roomsData,
                'allBranches' => $allBranches,
                'selectedBranchId' => $allBranches->first()?->id ?? null,
            ]);
        }
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'room_number' => [
                'required',
                'string',
                Rule::unique('rooms')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                }),
            ],
            'floor_number' => ['required', 'integer', 'min:0'],
            'sharing_type' => ['required', 'string'],
            'max_beds' => ['required', 'integer', 'min:1', 'max:20'],
            'is_ac' => ['required', 'boolean'],
            'rent' => ['required', 'numeric', 'min:0'],
            'deposit' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, &$room) {
            $room = Room::create([
                'branch_id' => $validated['branch_id'],
                'room_number' => $validated['room_number'],
                'floor_number' => $validated['floor_number'],
                'sharing_type' => $validated['sharing_type'],
                'max_beds' => $validated['max_beds'],
                'is_ac' => $validated['is_ac'],
                'status' => 'AVAILABLE',
            ]);

            for ($b = 1; $b <= $validated['max_beds']; $b++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_code' => 'Bed ' . $validated['room_number'] . '-' . chr(64 + $b),
                    'monthly_rent' => $validated['rent'],
                    'security_deposit' => $validated['deposit'],
                    'status' => 'AVAILABLE',
                ]);
            }
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created Room '.$room->room_number.' with '.$validated['max_beds'].' beds',
            'module' => 'ROOMS',
            'record_id' => $room->id,
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Room and beds created successfully.',
                'data' => $room->load('beds'),
            ]);
        }

        return redirect()->back()->with('success', 'Room and beds created successfully.');
    }

    public function students()
    {
        $studentsData = Student::with(['branch', 'room', 'bed', 'documents'])->latest()->get()->map(function ($student) {
            $profilePhotoDoc = $student->documents->firstWhere('doc_type', 'PROFILE_PHOTO');
            $aadhaarFrontDoc = $student->documents->firstWhere('doc_type', 'AADHAAR_FRONT');
            $aadhaarBackDoc = $student->documents->firstWhere('doc_type', 'AADHAAR_BACK');
            $panCardDoc = $student->documents->firstWhere('doc_type', 'PAN_CARD');
            
            $formatUrl = function (?string $path, string $docType) {
                if (!$path || str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) {
                    if ($docType === 'PROFILE_PHOTO') return 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=400';
                    if ($docType === 'AADHAAR_FRONT' || $docType === 'AADHAAR_BACK') return 'https://images.unsplash.com/photo-1554774853-d1d68e2bd6ec?auto=format&fit=crop&q=80&w=600';
                    return 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&q=80&w=600';
                }
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
                $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                    return asset('storage/' . $cleanPath);
                }
                // Fallback for seed mock files
                if (str_starts_with($path, 'uploads/')) {
                    return asset('storage/' . $cleanPath);
                }
                if ($docType === 'PROFILE_PHOTO') return 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=400';
                if ($docType === 'AADHAAR_FRONT' || $docType === 'AADHAAR_BACK') return 'https://images.unsplash.com/photo-1554774853-d1d68e2bd6ec?auto=format&fit=crop&q=80&w=600';
                return 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&q=80&w=600';
            };

            return [
                'id' => $student->app_reference ?? ('RES-'.str_pad($student->id, 4, '0', STR_PAD_LEFT)),
                'full_name' => $student->full_name,
                'phone' => $student->phone,
                'email' => $student->email ?? '',
                'aadhaar_number' => $student->aadhaar_number ?? '',
                'pan_number' => $student->pan_number ?? '',
                'parent_name' => $student->parent_name ?? '',
                'parent_phone' => $student->parent_phone ?? '',
                'emergency_contact' => $student->emergency_contact ?? '',
                'current_address' => $student->current_address ?? '',
                'initial_meter_reading' => (float)($student->initial_meter_reading ?? 0),
                'branch_name' => $student->branch ? $student->branch->name : 'N/A',
                'room_bed' => ($student->room ? 'Room '.$student->room->room_number : 'Unassigned').($student->bed ? ' ('.$student->bed->bed_code.')' : ''),
                'joining_date' => $student->joining_date ? $student->joining_date->format('d M Y') : 'N/A',
                'kyc_status' => (isset($student->kyc_status) && $student->kyc_status === 'APPROVED') ? 'VERIFIED' : ($student->kyc_status ?? 'PENDING'),
                'rent_status' => $student->rent_status ?? 'PENDING',
                'rent_balance_due' => (float)$student->effective_rent_due,
                'deposit_balance_due' => (float)($student->deposit_balance_due ?? 0),
                'status' => $student->status ?? 'PENDING',
                'profile_photo' => $formatUrl($profilePhotoDoc?->file_path, 'PROFILE_PHOTO'),
                'aadhaar_front' => $formatUrl($aadhaarFrontDoc?->file_path, 'AADHAAR_FRONT'),
                'aadhaar_back' => $formatUrl($aadhaarBackDoc?->file_path, 'AADHAAR_BACK'),
                'pan_card' => $formatUrl($panCardDoc?->file_path, 'PAN_CARD'),
                'db_id' => $student->id,
                'branch_id' => $student->branch_id,
                'raw_joining_date' => $student->joining_date ? $student->joining_date->format('Y-m-d') : '',
                'raw_kyc_status' => $student->kyc_status ?? 'PENDING',
                'raw_rent_status' => $student->rent_status ?? 'PENDING',
                'raw_status' => $student->status ?? 'PENDING',
            ];
        });

        $allBranches = Branch::all();

        return view('super_admin.students', ['students' => $studentsData, 'allBranches' => $allBranches]);
    }

    public function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'joining_date' => ['nullable', 'date'],
            'aadhaar_number' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:30'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'current_address' => ['nullable', 'string'],
            'initial_meter_reading' => ['nullable', 'numeric', 'min:0'],
            'kyc_status' => ['required', 'string'],
            'rent_status' => ['required', 'string'],
            'rent_balance_due' => ['nullable', 'numeric', 'min:0'],
            'deposit_balance_due' => ['nullable', 'numeric', 'min:0'],
        ]);

        $student->update($validated);

        if ($student->user) {
            $userUpdate = ['name' => $validated['full_name']];
            if (!empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            if (!empty($validated['phone'])) {
                $userUpdate['phone'] = $validated['phone'];
            }
            $student->user->update($userUpdate);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Student: '.$student->full_name.' (ID: '.$student->id.')',
            'module' => 'STUDENT',
            'record_id' => $student->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student record updated successfully.',
            'student' => $student,
        ]);
    }

    public function destroyStudent($id)
    {
        $student = Student::findOrFail($id);
        
        DB::transaction(function () use ($student) {
            if ($student->user) {
                $student->user->delete();
            }
            $student->delete();
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted Student: '.$student->full_name,
            'module' => 'STUDENT',
            'record_id' => $student->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student record deleted successfully.',
        ]);
    }

    public function finance()
    {
        $totalDepositsHeld = \App\Models\RoomAllocation::where('status', 'ACTIVE')->sum('security_deposit');
        if ($totalDepositsHeld == 0) {
            $totalDepositsHeld = \App\Models\Bed::where('status', 'OCCUPIED')->sum('security_deposit');
        }

        $financeSummary = [
            'total_collections_this_month' => '₹'.number_format(Payment::whereIn('status', ['PAID', 'VERIFIED'])->sum('amount')),
            'pending_rent_dues' => '₹'.number_format(Payment::where('status', 'PENDING')->sum('amount')),
            'total_security_deposits_held' => '₹'.number_format($totalDepositsHeld),
            'electricity_collections' => '₹'.number_format(ElectricityReading::where('status', 'APPROVED')->sum('total_amount')),
            'total_cash_in_hand' => '₹'.number_format(Payment::where('payment_mode', 'CASH')->whereIn('status', ['PAID', 'VERIFIED'])->sum('amount')),
        ];

        $subAdmins = User::where('role', 'SUB_ADMIN')->with('branches')->get();

        $managerCashLedger = $subAdmins->map(function ($subAdmin) {
            $branchIds = $subAdmin->branches->pluck('id');
            $cashPayments = Payment::with(['student', 'proof'])
                ->where('payment_mode', 'CASH')
                ->whereIn('branch_id', $branchIds)
                ->latest()
                ->get();

            $totalCash = $cashPayments->sum('amount');

            return [
                'manager_id' => $subAdmin->id,
                'manager_name' => $subAdmin->name,
                'manager_email' => $subAdmin->email,
                'branch_name' => $subAdmin->branches->first() ? $subAdmin->branches->first()->name : 'Naroda Branch',
                'total_cash_collected' => '₹'.number_format($totalCash),
                'total_cash_raw' => $totalCash,
                'transactions_count' => $cashPayments->count(),
                'recent_cash_entries' => $cashPayments->take(5)->map(function ($p) {
                    return [
                        'tx' => $p->tx_reference,
                        'student' => $p->student ? $p->student->full_name : 'Resident',
                        'amount' => '₹'.number_format($p->amount),
                        'date' => $p->created_at ? $p->created_at->format('d M Y, h:i A') : 'N/A',
                        'utr' => $p->proof ? $p->proof->utr_number : 'CASH',
                    ];
                }),
            ];
        });

        $formatUrl = function (?string $path) {
            if (!$path) return null;
            if (str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
            $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
            return null;
        };

        $transactionsData = Payment::with(['student', 'branch', 'proof'])->latest()->get()->map(function ($payment) use ($formatUrl) {
            return [
                'id' => $payment->id,
                'tx_reference' => $payment->tx_reference,
                'student_name' => $payment->student ? $payment->student->full_name : 'Resident',
                'branch_name' => $payment->branch ? $payment->branch->name : 'N/A',
                'payment_type' => str_replace('_', ' ', ucwords(strtolower($payment->payment_type))),
                'amount_val' => $payment->amount,
                'amount' => '₹'.number_format($payment->amount),
                'payment_mode' => $payment->payment_mode ?? 'UPI',
                'utr' => $payment->payment_mode === 'CASH' ? 'N/A' : ($payment->proof ? $payment->proof->utr_number : 'N/A'),
                'proof_image' => $payment->proof ? $formatUrl($payment->proof->screenshot_path) : null,
                'date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : 'N/A',
                'raw_date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : '',
                'status' => $payment->status,
                'verified_at' => (($payment->status === 'VERIFIED' || $payment->status === 'PAID') && $payment->paid_at) ? $payment->paid_at->format('d M Y, h:i A') : 'N/A',
            ];
        });

        return view('super_admin.finance', compact('financeSummary', 'managerCashLedger'), ['transactions' => $transactionsData]);
    }

    public function updateTransaction(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'payment_mode' => ['required', 'string'],
            'utr' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:PENDING,PAID,VERIFIED'],
        ]);

        DB::transaction(function () use ($payment, $validated) {
            $payment->update([
                'amount' => $validated['amount'],
                'due_date' => $validated['due_date'],
                'payment_mode' => $validated['payment_mode'],
                'status' => $validated['status'],
                'paid_at' => ($validated['status'] === 'VERIFIED' || $validated['status'] === 'PAID') ? ($payment->paid_at ?? now()) : null,
            ]);

            if ($payment->proof) {
                $payment->proof->update([
                    'utr_number' => $validated['utr'] ?? 'N/A',
                    'status' => $validated['status'] === 'VERIFIED' ? 'VERIFIED' : 'PENDING',
                ]);
            } else if ($validated['utr']) {
                PaymentProof::create([
                    'payment_id' => $payment->id,
                    'utr_number' => $validated['utr'],
                    'screenshot_path' => 'uploads/proofs/cash_receipt.png',
                    'status' => $validated['status'] === 'VERIFIED' ? 'VERIFIED' : 'PENDING',
                    'verified_by' => $validated['status'] === 'VERIFIED' ? Auth::id() : null,
                ]);
            }

            if ($student = $payment->student) {
                $student->update([
                    'rent_status' => $validated['status'] === 'VERIFIED' ? 'PAID' : 'PENDING',
                ]);
            }
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Payment ID: '.$payment->id,
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction record updated successfully.',
        ]);
    }

    public function destroyTransaction($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted Payment ID: '.$payment->id,
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction record deleted successfully.',
        ]);
    }

    public function toggleTransactionStatus(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $oldStatus = $payment->status;
        
        if ($oldStatus === 'PENDING') {
            $newStatus = 'VERIFIED';
        } elseif ($oldStatus === 'VERIFIED' || $oldStatus === 'PAID') {
            $newStatus = 'REJECTED';
        } else {
            $newStatus = 'PENDING';
        }

        DB::transaction(function () use ($payment, $newStatus) {
            $payment->update([
                'status' => $newStatus,
                'paid_at' => ($newStatus === 'VERIFIED') ? ($payment->paid_at ?? now()) : null,
            ]);

            if ($payment->proof) {
                $payment->proof->update([
                    'status' => $newStatus,
                    'verified_by' => $newStatus === 'VERIFIED' ? auth()->id() : null,
                ]);
            }

            if ($student = $payment->student) {
                $student->update([
                    'rent_status' => $newStatus === 'VERIFIED' ? 'PAID' : 'DUE',
                ]);
            }
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Toggled Payment ID: '.$payment->id.' status from '.$oldStatus.' to '.$newStatus,
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated to ' . $newStatus,
            'new_status' => $newStatus,
            'verified_at' => $newStatus === 'VERIFIED' ? now()->format('d M Y, h:i A') : 'N/A'
        ]);
    }

    public function settings()
    {
        $auditLogs = AuditLog::with('user')->latest()->take(200)->get()->map(function ($log) {
            return [
                'id' => 'LOG-'.str_pad($log->id, 4, '0', STR_PAD_LEFT),
                'timestamp' => $log->created_at ? $log->created_at->format('d M Y H:i:s') : 'N/A',
                'user' => $log->user ? $log->user->name : 'System Auto',
                'action' => $log->action,
                'module' => $log->module ?? 'SYSTEM',
                'ip' => $log->ip_address ?? '127.0.0.1',
            ];
        });

        return view('super_admin.settings', compact('auditLogs'));
    }

    public function getSidebarCounts()
    {
        return response()->json([
            'pending_registrations' => \App\Models\RegistrationRequest::whereIn('status', ['PENDING', 'pending'])->count(),
            'pending_payments' => \App\Models\PaymentProof::whereIn('status', ['PENDING', 'pending'])->count(),
            'pending_complaints' => \App\Models\Complaint::whereNotIn('status', ['RESOLVED', 'CLOSED', 'Resolved', 'Solved'])->count(),
            'pending_password_resets' => \App\Models\PasswordResetRequest::where('status', 'PENDING')->count(),
        ]);
    }

    public function passwordRequests(Request $request)
    {
        $requests = PasswordResetRequest::with(['user', 'student.room', 'student.bed', 'branch', 'resolver'])
            ->latest()
            ->get();

        $allStudents = Student::with(['user', 'room', 'bed', 'branch'])
            ->where('status', 'APPROVED')
            ->orderBy('full_name')
            ->get();

        return view('super_admin.password_requests', compact('requests', 'allStudents'));
    }

    public function resetPasswordRequest($id, Request $request)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $resetReq = PasswordResetRequest::with(['user', 'student'])->findOrFail($id);

        $user = $resetReq->user;
        $user->password = Hash::make($validated['new_password']);
        $user->tokens()->delete();
        $user->save();

        $resetReq->update([
            'status' => 'RESOLVED',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Super Admin reset student password via request ID: '.$resetReq->id.' for User: '.$user->name.' ('.$user->phone.')',
            'module' => 'RESIDENT_SECURITY',
            'record_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
                'new_password' => $validated['new_password'],
                'user_name' => $user->name,
                'phone' => $user->phone,
            ]);
        }

        return back()->with('success', "Password for {$user->name} ({$user->phone}) has been updated to: {$validated['new_password']}");
    }

    public function directResetPassword($studentId, Request $request)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $student = Student::with('user')->findOrFail($studentId);

        $last10 = $student->phone ? substr(preg_replace('/[^0-9]/', '', $student->phone), -10) : null;

        $matchingUsers = collect();
        if ($student->user) {
            $matchingUsers->push($student->user);
        }
        if ($last10) {
            $byPhone = User::whereRaw('SUBSTR(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), -10) = ?', [$last10])->get();
            $matchingUsers = $matchingUsers->merge($byPhone);
        }
        if ($student->email) {
            $byEmail = User::where('email', $student->email)->get();
            $matchingUsers = $matchingUsers->merge($byEmail);
        }
        $matchingUsers = $matchingUsers->unique('id');

        if ($matchingUsers->isEmpty()) {
            $user = User::create([
                'name' => $student->full_name,
                'email' => $student->email ?: ($student->phone . '@rudrapg.com'),
                'phone' => $student->phone,
                'password' => Hash::make($validated['new_password']),
                'role' => 'STUDENT',
                'status' => 'ACTIVE',
            ]);
            $student->user_id = $user->id;
            $student->save();
        } else {
            $primaryUser = $matchingUsers->firstWhere('id', $student->user_id) 
                        ?? $matchingUsers->firstWhere('role', 'STUDENT') 
                        ?? $matchingUsers->first();

            if ($student->user_id !== $primaryUser->id) {
                $student->user_id = $primaryUser->id;
                $student->save();
            }

            if (str_contains(strtolower($primaryUser->name), 'demo') || str_contains(strtolower($primaryUser->email), 'demo')) {
                $primaryUser->name = $student->full_name;
                if ($student->email && !str_contains($student->email, 'demo')) {
                    $primaryUser->email = $student->email;
                }
            }

            foreach ($matchingUsers as $u) {
                $u->password = Hash::make($validated['new_password']);
                $u->status = 'ACTIVE';
                $u->tokens()->delete();
                $u->save();
            }
            $user = $primaryUser;
        }

        $pendingReq = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingReq) {
            $pendingReq->update([
                'status' => 'RESOLVED',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
                'notes' => 'Resolved directly by ' . Auth::user()->name,
            ]);
        } else {
            PasswordResetRequest::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'phone' => $user->phone ?? $student->phone,
                'status' => 'RESOLVED',
                'notes' => 'Direct password update by ' . Auth::user()->name,
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Super Admin direct reset student password for: '.$user->name.' ('.$user->phone.')',
            'module' => 'RESIDENT_SECURITY',
            'record_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
                'new_password' => $validated['new_password'],
                'user_name' => $user->name,
                'phone' => $user->phone,
            ]);
        }

        return back()->with('success', "Password for {$user->name} ({$user->phone}) has been updated to: {$validated['new_password']}");
    }
}
