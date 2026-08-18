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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'total_revenue' => '₹'.number_format(Payment::whereIn('status', ['PAID', 'VERIFIED'])->sum('amount')),
            'occupancy_rate' => Bed::count() > 0 ? round((Bed::where('status', 'OCCUPIED')->count() / Bed::count()) * 100, 1).'%' : '0%',
            'active_branches' => Branch::where('status', 'ACTIVE')->count(),
            'pending_approvals' => RegistrationRequest::where('status', 'PENDING')->count(),
            'total_branches' => Branch::count(),
            'total_students' => Student::where('status', 'APPROVED')->count(),
            'total_rooms' => Room::count(),
            'total_beds' => Bed::count(),
            'occupied_beds' => Bed::where('status', 'OCCUPIED')->count(),
            'available_beds' => Bed::where('status', 'AVAILABLE')->count(),
            'monthly_collection' => '₹'.number_format(Payment::whereIn('status', ['PAID', 'VERIFIED'])->sum('amount')),
            'pending_payments' => Payment::where('status', 'PENDING')->count(),
            'active_complaints' => Complaint::whereIn('status', ['PENDING', 'IN_PROGRESS'])->count(),
        ];

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

        return view('super_admin.dashboard', compact('metrics', 'recentRegistrations', 'recentPayments', 'branches', 'recentActivities'));
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
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'manager_name' => ['required', 'string'],
            'manager_phone' => ['required', 'string'],
            'electricity_unit_rate' => ['required', 'numeric', 'min:0'],
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
            'floor_number' => ['required', 'integer', 'min:1'],
            'sharing_type' => ['required', 'string'],
            'is_ac' => ['required', 'boolean'],
            'rent' => ['required', 'numeric', 'min:0'],
            'deposit' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($room, $validated) {
            $room->update([
                'branch_id' => $validated['branch_id'],
                'room_number' => $validated['room_number'],
                'floor_number' => $validated['floor_number'],
                'sharing_type' => $validated['sharing_type'],
                'is_ac' => $validated['is_ac'],
            ]);

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
            'action' => 'Updated Room '.$room->room_number,
            'module' => 'ROOMS',
            'record_id' => $room->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Room updated successfully.',
        ]);
    }

    public function destroyRoom($id)
    {
        $room = Room::findOrFail($id);
        
        DB::transaction(function () use ($room) {
            $room->beds()->delete();
            $room->delete();
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted Room '.$room->room_number,
            'module' => 'ROOMS',
            'record_id' => $room->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Room deleted successfully.',
        ]);
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'branches' => ['required', 'array', 'min:1'],
            'branches.*' => ['exists:branches,id'],
        ]);

        DB::transaction(function () use ($validated, &$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'SUB_ADMIN',
                'status' => 'ACTIVE',
            ]);

            $user->branches()->sync($validated['branches']);
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created Sub Admin: '.$user->email,
            'module' => 'USERS',
            'record_id' => $user->id,
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
            'created_at' => $user->created_at->format('d M Y'),
            'db_id' => $user->id,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Sub Admin account created successfully! Account is now active.',
                'data' => $responseData,
            ]);
        }

        return redirect()->back()->with('success', 'Sub Admin account created successfully.');
    }

    public function updateSubAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'branches' => ['required', 'array', 'min:1'],
            'branches.*' => ['exists:branches,id'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            if (!empty($validated['password'])) {
                $user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $user->branches()->sync($validated['branches']);
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Sub Admin: '.$user->email,
            'module' => 'USERS',
            'record_id' => $user->id,
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
            'created_at' => $user->created_at->format('d M Y'),
            'db_id' => $user->id,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin account updated successfully.',
            'data' => $responseData,
        ]);
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
        $user->delete();

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

    public function roomsMaster()
    {
        $roomsData = Room::with(['branch', 'beds'])->get()->map(function ($room) {
            $totalBeds = $room->beds->count() > 0 ? $room->beds->count() : $room->max_beds;
            $occupiedBeds = $room->beds->where('status', 'OCCUPIED')->count();
            $availableBeds = max(0, $totalBeds - $occupiedBeds);

            return [
                'id' => $room->id,
                'room_number' => (string) $room->room_number,
                'floor' => $room->floor_number,
                'sharing_type' => $room->sharing_type,
                'is_ac' => (bool) $room->is_ac,
                'rent' => $room->beds->first() ? (int) $room->beds->first()->monthly_rent : 0,
                'deposit' => $room->beds->first() ? (int) $room->beds->first()->security_deposit : 0,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $availableBeds,
                'status' => $availableBeds == 0 ? 'Full' : 'Available',
                'branch_id' => $room->branch_id,
                'max_beds' => $room->max_beds,
            ];
        })->toArray();

        $allBranches = Branch::all();

        return view('super_admin.rooms_master', ['rooms' => $roomsData, 'allBranches' => $allBranches]);
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'room_number' => ['required', 'string'],
            'floor_number' => ['required', 'integer', 'min:1'],
            'sharing_type' => ['required', 'string'],
            'max_beds' => ['required', 'integer', 'min:1', 'max:6'],
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
                    'bed_code' => 'Bed '.$validated['floor_number'].chr(64 + $b),
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
                'branch_name' => $student->branch ? $student->branch->name : 'N/A',
                'room_bed' => ($student->room ? 'Room '.$student->room->room_number : 'Unassigned').($student->bed ? ' ('.$student->bed->bed_code.')' : ''),
                'joining_date' => $student->joining_date ? $student->joining_date->format('d M Y') : 'N/A',
                'kyc_status' => (isset($student->kyc_status) && $student->kyc_status === 'APPROVED') ? 'VERIFIED' : ($student->kyc_status ?? 'PENDING'),
                'rent_status' => $student->rent_status ?? 'PENDING',
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
            'full_name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
            'kyc_status' => ['required', 'string'],
            'rent_status' => ['required', 'string'],
            'joining_date' => ['nullable', 'date'],
        ]);

        $student->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Student: '.$student->full_name,
            'module' => 'STUDENT',
            'record_id' => $student->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student record updated successfully.',
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
        ]);
    }
}
