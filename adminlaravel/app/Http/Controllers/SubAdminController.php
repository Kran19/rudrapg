<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubAdminController extends Controller
{
    public function dashboard()
    {
        $branch = Branch::first();

        $branchInfo = [
            'name' => $branch ? $branch->name : 'All Branches',
            'code' => $branch ? $branch->code : 'PG-ALL',
            'manager' => $branch ? $branch->manager_name : 'N/A',
            'total_rooms' => Room::count(),
            'total_beds' => Bed::count(),
            'occupied_beds' => Bed::where('status', 'OCCUPIED')->count(),
            'available_beds' => Bed::where('status', 'AVAILABLE')->count(),
            'pending_verifications' => RegistrationRequest::where('status', 'PENDING')->count(),
            'overdue_rents' => Payment::where('status', 'PENDING')->count(),
            'open_complaints' => Complaint::whereIn('status', ['PENDING', 'IN_PROGRESS'])->count(),
        ];

        $pendingVerifications = RegistrationRequest::with(['student.documents', 'student.room', 'student.bed'])
            ->where('status', 'PENDING')
            ->latest()
            ->get()
            ->map(function ($req) {
                $student = $req->student;
                return [
                    'id' => $req->app_reference,
                    'student_name' => $student ? $student->full_name : 'Applicant',
                    'phone' => $student ? $student->phone : 'N/A',
                    'room' => ($student && $student->room ? 'Room '.$student->room->room_number : 'Unassigned').($student && $student->bed ? ' ('.$student->bed->bed_code.')' : ''),
                    'rent' => $student && $student->bed ? '₹'.number_format($student->bed->monthly_rent) : 'N/A',
                    'deposit' => $student && $student->bed ? '₹'.number_format($student->bed->security_deposit) : 'N/A',
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                ];
            });

        return view('sub_admin.dashboard', compact('branchInfo', 'pendingVerifications'));
    }

    public function verifications()
    {
        $queue = RegistrationRequest::with(['student.documents', 'student.room', 'student.bed', 'branch'])
            ->latest()
            ->get()
            ->map(function ($req) {
                $student = $req->student;
                return [
                    'id' => $req->app_reference,
                    'db_id' => $req->id,
                    'student_name' => $student ? $student->full_name : 'Applicant',
                    'phone' => $student ? $student->phone : 'N/A',
                    'aadhaar' => $student ? $student->aadhaar_number : 'N/A',
                    'pan' => $student ? ($student->pan_number ?? 'N/A') : 'N/A',
                    'room_number' => $student && $student->room ? $student->room->room_number : 'Unassigned',
                    'bed_code' => $student && $student->bed ? $student->bed->bed_code : 'Unassigned',
                    'sharing_type' => $student && $student->room ? $student->room->sharing_type : 'Standard',
                    'rent' => $student && $student->bed ? '₹'.number_format($student->bed->monthly_rent) : '₹0',
                    'deposit' => $student && $student->bed ? '₹'.number_format($student->bed->security_deposit) : '₹0',
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                    'status' => $req->status == 'PENDING' ? 'Pending Verification' : $req->status,
                    'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                    'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                    'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80',
                ];
            });

        return view('sub_admin.verifications', compact('queue'));
    }

    public function approveVerification($id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();

        DB::transaction(function () use ($requestRecord) {
            $requestRecord->update([
                'status' => 'APPROVED',
                'processed_by' => Auth::id(),
            ]);

            if ($student = $requestRecord->student) {
                $student->update([
                    'status' => 'APPROVED',
                    'kyc_status' => 'VERIFIED',
                ]);

                if (!$student->bed_id) {
                    $availableBed = \App\Models\Bed::where('status', 'AVAILABLE')->first();
                    if ($availableBed) {
                        $student->update([
                            'room_id' => $availableBed->room_id,
                            'bed_id' => $availableBed->id
                        ]);
                        $student->refresh();
                    }
                }

                if ($student->bed) {
                    $student->bed->update(['status' => 'OCCUPIED']);

                    RoomAllocation::create([
                        'branch_id' => $student->branch_id,
                        'student_id' => $student->id,
                        'room_id' => $student->room_id,
                        'bed_id' => $student->bed_id,
                        'start_date' => now()->toDateString(),
                        'monthly_rent' => $student->bed->monthly_rent,
                        'security_deposit' => $student->bed->security_deposit,
                        'status' => 'ACTIVE',
                        'allocated_by' => Auth::id(),
                    ]);
                }
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Approved Verification & Key Handover: '.$id,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student booking approved and key handover record generated.',
        ]);
    }

    public function rejectVerification($id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();

        DB::transaction(function () use ($requestRecord) {
            $requestRecord->update([
                'status' => 'REJECTED',
                'processed_by' => Auth::id(),
            ]);

            if ($student = $requestRecord->student) {
                $student->update(['status' => 'REJECTED']);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rejected Registration Request: '.$id,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Application rejected successfully.',
        ]);
    }

    public function bedMap()
    {
        $roomsData = Room::with('beds.student')->orderBy('floor_number')->orderBy('room_number')->get()->map(function ($room) {
            $totalBeds = $room->beds->count() > 0 ? $room->beds->count() : $room->max_beds;
            $occupiedBeds = $room->beds->where('status', 'OCCUPIED')->count();
            $availableBeds = max(0, $totalBeds - $occupiedBeds);

            $bedsData = $room->beds->map(function ($bed) {
                return [
                    'code' => $bed->bed_code,
                    'status' => strtolower($bed->status),
                    'student_name' => $bed->student ? $bed->student->full_name : null,
                ];
            })->toArray();

            return [
                'id' => $room->id,
                'room_number' => (string) $room->room_number,
                'floor' => $room->floor_number,
                'sharing_type' => $room->sharing_type,
                'is_ac' => (bool) $room->is_ac,
                'rent' => $room->beds->first() ? (int) $room->beds->first()->monthly_rent : 0,
                'total_beds' => $totalBeds,
                'available_beds' => $availableBeds,
                'beds' => $bedsData,
            ];
        })->toArray();

        return view('sub_admin.bed_map', ['rooms' => $roomsData]);
    }

    public function rentLedger()
    {
        $duesData = Payment::with(['student', 'branch', 'proof'])->latest()->get()->map(function ($payment) {
            return [
                'resident_id' => $payment->student ? $payment->student->app_reference : 'N/A',
                'student_name' => $payment->student ? $payment->student->full_name : 'Resident',
                'room' => $payment->student && $payment->student->room ? $payment->student->room->room_number.' ('.($payment->student->bed ? $payment->student->bed->bed_code : 'Unassigned').')' : 'Unassigned',
                'rent' => '₹'.number_format($payment->amount),
                'due_date' => $payment->due_date ? $payment->due_date->format('d M Y') : 'N/A',
                'status' => $payment->status == 'VERIFIED' ? 'Paid' : ($payment->status == 'PENDING' ? 'Pending Verification' : $payment->status),
                'payment_mode' => $payment->payment_mode ?? 'UPI Transfer',
                'utr' => $payment->proof ? $payment->proof->utr_number : 'N/A',
            ];
        });

        $students = Student::with(['room', 'bed'])->where('status', 'APPROVED')->get();

        return view('sub_admin.rent_ledger', ['dues' => $duesData, 'students' => $students]);
    }

    public function recordCashPayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ]);

        $student = Student::findOrFail($validated['student_id']);

        DB::transaction(function () use ($validated, $student) {
            $payment = Payment::create([
                'tx_reference' => 'PAY-'.date('Y').'-'.rand(1000, 9999),
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'payment_type' => $validated['payment_type'],
                'amount' => $validated['amount'],
                'payment_mode' => 'CASH',
                'payment_date' => now()->toDateString(),
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

            PaymentProof::create([
                'payment_id' => $payment->id,
                'utr_number' => 'CASH/'.rand(100000000000, 999999999999),
                'screenshot_path' => 'uploads/proofs/cash_receipt.png',
                'status' => 'VERIFIED',
                'verified_by' => Auth::id(),
            ]);
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Recorded Cash Payment ₹'.$validated['amount'].' for '.$student->full_name,
            'module' => 'FINANCE',
            'record_id' => $student->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Offline cash payment receipt generated successfully!',
        ]);
    }

    public function electricityAudit()
    {
        $readingsData = ElectricityReading::with(['student', 'room', 'branch'])->latest()->get()->map(function ($reading) {
            return [
                'id' => $reading->id,
                'code' => 'E-2026-'.str_pad($reading->id, 3, '0', STR_PAD_LEFT),
                'student' => $reading->student ? $reading->student->full_name : 'Resident',
                'room' => $reading->room ? $reading->room->room_number : 'N/A',
                'prev_reading' => $reading->previous_reading,
                'curr_reading' => $reading->current_reading,
                'units' => $reading->units_consumed,
                'rate' => '₹'.number_format($reading->unit_rate, 2),
                'total' => '₹'.number_format($reading->total_amount),
                'photo_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80',
                'date' => $reading->created_at ? $reading->created_at->format('d M Y') : 'N/A',
                'status' => $reading->status == 'APPROVED' ? 'Approved' : 'Pending Audit',
            ];
        });

        return view('sub_admin.electricity_audit', ['readings' => $readingsData]);
    }

    public function approveElectricityReading($id)
    {
        $reading = ElectricityReading::findOrFail($id);

        $reading->update([
            'status' => 'APPROVED',
            'audited_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Approved Electricity Bill ID: '.$id,
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity bill approved and added to dues!',
        ]);
    }

    public function rejectElectricityReading($id)
    {
        $reading = ElectricityReading::findOrFail($id);

        $reading->update([
            'status' => 'REJECTED',
            'audited_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rejected Electricity Meter Reading ID: '.$id,
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity meter reading rejected.',
        ]);
    }

    public function complaints()
    {
        $ticketsData = Complaint::with(['student', 'room', 'branch'])->latest()->get()->map(function ($complaint) {
            return [
                'ticket' => $complaint->ticket_number,
                'student' => $complaint->student ? $complaint->student->full_name : 'Resident',
                'room' => $complaint->room ? $complaint->room->room_number : 'N/A',
                'category' => $complaint->category,
                'title' => $complaint->subject,
                'priority' => ucfirst(strtolower($complaint->priority)),
                'date' => $complaint->created_at ? $complaint->created_at->format('d M Y') : 'N/A',
                'status' => $complaint->status == 'RESOLVED' ? 'Resolved' : ($complaint->status == 'IN_PROGRESS' ? 'In Progress' : 'Open'),
            ];
        });

        return view('sub_admin.complaints', ['tickets' => $ticketsData]);
    }

    public function broadcastNotice(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string'],
            'is_important' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::first();

        $announcement = Announcement::create([
            'branch_id' => $branch ? $branch->id : null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => strtoupper(str_replace(' ', '_', $validated['category'])),
            'is_important' => $request->boolean('is_important', true),
            'created_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Broadcasted Branch Notice: '.$validated['title'],
            'module' => 'ANNOUNCEMENT',
            'record_id' => $announcement->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notice broadcasted to Flutter Student Mobile App!',
            'data' => $announcement,
        ]);
    }
}
