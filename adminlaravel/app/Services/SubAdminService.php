<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubAdminService
{
    public function getPendingVerifications(User $subAdmin)
    {
        $branchIds = $subAdmin->branches->pluck('id');

        return RegistrationRequest::with(['student.documents', 'student.room', 'student.bed', 'branch'])
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'PENDING')
            ->latest()
            ->get();
    }

    public function approveRegistration(int $requestId, array $data, User $subAdmin): Student
    {
        return DB::transaction(function () use ($requestId, $data, $subAdmin) {
            $req = RegistrationRequest::findOrFail($requestId);
            $student = $req->student;

            $room = Room::findOrFail($data['room_id']);
            $bed = Bed::findOrFail($data['bed_id']);

            // Allocate Bed
            $bed->update(['status' => 'OCCUPIED']);

            $joiningDate = $data['joining_date'] ?? now()->toDateString();

            $student->update([
                'room_id' => $room->id,
                'bed_id' => $bed->id,
                'joining_date' => $joiningDate,
                'kyc_status' => 'VERIFIED',
                'deposit_status' => 'VERIFIED',
                'status' => 'APPROVED',
            ]);

            // Create Room Allocation History Record
            RoomAllocation::create([
                'student_id' => $student->id,
                'branch_id' => $req->branch_id,
                'room_id' => $room->id,
                'bed_id' => $bed->id,
                'start_date' => $joiningDate,
                'monthly_rent' => $bed->monthly_rent,
                'security_deposit' => $bed->security_deposit,
                'status' => 'ACTIVE',
                'allocated_by' => $subAdmin->id,
            ]);

            // Create Notification
            Notification::create([
                'user_id' => $student->user_id,
                'title' => 'Registration Approved',
                'body' => 'Your registration ('.$req->app_reference.') has been approved. Allocated Room '.$room->room_number.' (Bed '.$bed->bed_code.').',
                'type' => 'REGISTRATION',
                'read_at' => null,
            ]);

            $req->update([
                'status' => 'APPROVED',
                'processed_by' => $subAdmin->id,
                'processed_at' => now(),
            ]);

            return $student;
        });
    }

    public function rejectRegistration(int $requestId, string $reason, User $subAdmin): RegistrationRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $subAdmin) {
            $req = RegistrationRequest::findOrFail($requestId);
            $student = $req->student;

            $student->update(['status' => 'REJECTED']);

            $req->update([
                'status' => 'REJECTED',
                'rejection_reason' => $reason,
                'processed_by' => $subAdmin->id,
                'processed_at' => now(),
            ]);

            // Create Notification for Student with mandatory rejection reason
            Notification::create([
                'user_id' => $student->user_id,
                'title' => 'Registration Request Rejected',
                'body' => 'Your registration ('.$req->app_reference.') was not approved. Remarks: '.$reason,
                'type' => 'REGISTRATION',
                'read_at' => null,
            ]);

            return $req;
        });
    }

    public function getBedMapMatrix(User $subAdmin)
    {
        $branchIds = $subAdmin->branches->pluck('id');

        return Room::with('beds.student')
            ->whereIn('branch_id', $branchIds)
            ->orderBy('floor_number')
            ->orderBy('room_number')
            ->get();
    }

    public function verifyPayment(int $paymentId, User $subAdmin): Payment
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->update(['status' => 'VERIFIED', 'paid_at' => now()]);

        if ($payment->proof) {
            $payment->proof->update(['status' => 'VERIFIED', 'verified_by' => $subAdmin->id, 'verified_at' => now()]);
        }

        return $payment;
    }

    public function auditElectricity(int $readingId, User $subAdmin): ElectricityReading
    {
        $reading = ElectricityReading::findOrFail($readingId);
        $reading->update(['status' => 'APPROVED', 'audited_by' => $subAdmin->id]);

        return $reading;
    }

    public function resolveComplaint(int $complaintId, User $subAdmin): Complaint
    {
        $complaint = Complaint::findOrFail($complaintId);
        $complaint->update(['status' => 'RESOLVED', 'resolved_by' => $subAdmin->id, 'resolved_at' => now()]);

        return $complaint;
    }
}
