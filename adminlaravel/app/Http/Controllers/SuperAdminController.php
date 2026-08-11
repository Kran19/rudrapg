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

    public function subAdmins()
    {
        $subAdminsData = User::where('role', 'SUB_ADMIN')->with('branches')->get()->map(function ($user) {
            return [
                'id' => 'SA-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'assigned_branches' => $user->branches->pluck('name')->toArray(),
                'status' => $user->status ?? 'Active',
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : 'N/A',
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
            'created_at' => $user->created_at->format('d M Y'),
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
        $studentsData = Student::with(['branch', 'room', 'bed'])->latest()->get()->map(function ($student) {
            return [
                'id' => $student->app_reference ?? ('RES-'.str_pad($student->id, 4, '0', STR_PAD_LEFT)),
                'full_name' => $student->full_name,
                'phone' => $student->phone,
                'branch_name' => $student->branch ? $student->branch->name : 'N/A',
                'room_bed' => ($student->room ? 'Room '.$student->room->room_number : 'Unassigned').($student->bed ? ' ('.$student->bed->bed_code.')' : ''),
                'joining_date' => $student->joining_date ? $student->joining_date->format('d M Y') : 'N/A',
                'kyc_status' => $student->kyc_status ?? 'PENDING',
                'rent_status' => $student->rent_status ?? 'PENDING',
                'status' => $student->status ?? 'PENDING',
            ];
        });

        return view('super_admin.students', ['students' => $studentsData]);
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

        $transactionsData = Payment::with(['student', 'branch', 'proof'])->latest()->get()->map(function ($payment) {
            return [
                'tx_reference' => $payment->tx_reference,
                'student_name' => $payment->student ? $payment->student->full_name : 'Resident',
                'branch_name' => $payment->branch ? $payment->branch->name : 'N/A',
                'payment_type' => $payment->payment_type,
                'amount' => '₹'.number_format($payment->amount),
                'payment_mode' => $payment->payment_mode ?? 'UPI',
                'utr' => $payment->proof ? $payment->proof->utr_number : 'N/A',
                'date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : 'N/A',
                'status' => $payment->status,
            ];
        });

        return view('super_admin.finance', compact('financeSummary', 'managerCashLedger'), ['transactions' => $transactionsData]);
    }

    public function settings()
    {
        $auditLogs = AuditLog::with('user')->latest()->take(50)->get()->map(function ($log) {
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
}
