<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\RegistrationRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function registerFromQr(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $branch = Branch::where('code', $data['branch_code'])->firstOrFail();

            // Create Student User Account
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password'] ?? 'secret123'),
                'role' => 'STUDENT',
                'status' => 'ACTIVE',
            ]);

            $appRef = 'REG-'.date('Y').'-'.rand(1000, 9999);

            // Create Student Record
            $student = Student::create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'app_reference' => $appRef,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'aadhaar_number' => $data['aadhaar_number'],
                'pan_number' => $data['pan_number'] ?? null,
                'parent_name' => $data['parent_name'],
                'parent_phone' => $data['parent_phone'],
                'emergency_contact' => $data['parent_phone'],
                'current_address' => $data['current_address'],
                'kyc_status' => 'PENDING',
                'rent_status' => 'DUE',
                'deposit_status' => 'PENDING',
                'status' => 'PENDING_APPROVAL',
            ]);

            // Save Document Placeholders
            StudentDocument::create(['student_id' => $student->id, 'doc_type' => 'PROFILE_PHOTO', 'file_path' => $data['profile_photo_path'] ?? 'uploads/kyc/profile.jpg']);
            StudentDocument::create(['student_id' => $student->id, 'doc_type' => 'AADHAAR_FRONT', 'file_path' => $data['aadhaar_front_path'] ?? 'uploads/kyc/aadhaar_front.jpg']);
            StudentDocument::create(['student_id' => $student->id, 'doc_type' => 'PAN_CARD', 'file_path' => $data['pan_card_path'] ?? 'uploads/kyc/pan.jpg']);

            // Create Verification Queue Entry
            RegistrationRequest::create([
                'branch_id' => $branch->id,
                'student_id' => $student->id,
                'app_reference' => $appRef,
                'status' => 'PENDING',
            ]);

            return $student;
        });
    }

    public function getProfile(User $user): ?Student
    {
        return Student::with(['branch', 'room', 'bed', 'documents'])
            ->where('user_id', $user->id)
            ->first();
    }

    public function uploadPaymentProof(Student $student, array $data): Payment
    {
        return DB::transaction(function () use ($student, $data) {
            $txRef = 'PAY-'.date('Y').'-'.rand(1000, 9999);

            $payment = Payment::create([
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'tx_reference' => $txRef,
                'payment_type' => $data['payment_type'] ?? 'RENT',
                'amount' => $data['amount'] ?? 6500.00,
                'payment_mode' => 'UPI',
                'payment_date' => now()->toDateString(),
                'status' => 'PENDING',
            ]);

            PaymentProof::create([
                'payment_id' => $payment->id,
                'utr_number' => $data['utr_number'],
                'screenshot_path' => $data['screenshot_path'] ?? 'uploads/proofs/proof.png',
                'status' => 'PENDING',
            ]);

            return $payment;
        });
    }

    public function submitElectricityReading(Student $student, array $data): ElectricityReading
    {
        $lastReading = ElectricityReading::where('student_id', $student->id)->latest()->first();
        $prevReading = $lastReading ? $lastReading->current_reading : 14475;
        $currReading = (int) $data['current_reading'];
        $units = max(0, $currReading - $prevReading);
        $rate = $student->branch ? $student->branch->electricity_unit_rate : 10.0;
        $total = $units * $rate;

        return ElectricityReading::create([
            'branch_id' => $student->branch_id,
            'room_id' => $student->room_id,
            'student_id' => $student->id,
            'reading_month' => date('F Y'),
            'current_reading' => $currReading,
            'previous_reading' => $prevReading,
            'units_consumed' => $units,
            'unit_rate' => $rate,
            'total_amount' => $total,
            'meter_photo_path' => $data['meter_photo_path'] ?? 'uploads/meter/reading.jpg',
            'status' => 'PENDING',
        ]);
    }

    public function createComplaint(Student $student, array $data): Complaint
    {
        $ticket = 'TKT-'.date('Y').'-'.rand(100, 999);

        return Complaint::create([
            'branch_id' => $student->branch_id,
            'student_id' => $student->id,
            'room_id' => $student->room_id,
            'ticket_number' => $ticket,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'MEDIUM',
            'status' => 'PENDING',
        ]);
    }

    public function getNotices(Student $student)
    {
        return Announcement::where('branch_id', $student->branch_id)
            ->orWhereNull('branch_id')
            ->latest()
            ->get();
    }
}
