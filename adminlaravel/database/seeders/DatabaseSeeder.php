<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@rudrapg.com'],
            [
                'name' => 'Super Administrator',
                'phone' => '+91 98980 00000',
                'password' => Hash::make('password'),
                'role' => 'SUPER_ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        // 2. Sub Admin User
        $subAdmin = User::firstOrCreate(
            ['email' => 'subadmin.naroda@rudrapg.com'],
            [
                'name' => 'Suresh Patel (Naroda Manager)',
                'phone' => '+91 98765 43210',
                'password' => Hash::make('password'),
                'role' => 'SUB_ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        // 3. Student User
        $studentUser = User::firstOrCreate(
            ['email' => 'rahul.sharma@gmail.com'],
            [
                'name' => 'Rahul Sharma',
                'phone' => '+91 98765 43211',
                'password' => Hash::make('password'),
                'role' => 'STUDENT',
                'status' => 'ACTIVE',
            ]
        );

        // 4. Naroda Branch Master
        $branch = Branch::firstOrCreate(
            ['code' => 'PG-NRD-01'],
            [
                'name' => 'Naroda Branch',
                'address' => '102, Main Highway Road, Opposite Science City Gate, Naroda, Ahmedabad',
                'city' => 'Ahmedabad',
                'phone' => '+91 79228 11223',
                'email' => 'naroda@rudrapg.com',
                'manager_name' => 'Suresh Patel',
                'manager_phone' => '+91 98765 43210',
                'electricity_unit_rate' => 10.00,
                'qr_code_hash' => 'hash_naroda_88129931',
                'status' => 'ACTIVE',
            ]
        );

        // Assign Sub Admin to Naroda Branch
        $subAdmin->branches()->syncWithoutDetaching([$branch->id]);

        // 5. Rooms & Beds Master (4 Floors, 40 Rooms, 100 Beds)
        $firstRoom = null;
        $firstBed = null;

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($r = 1; $r <= 10; $r++) {
                $roomNum = sprintf('%d%02d', $floor, $r);
                $room = Room::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'room_number' => (string) $roomNum,
                    ],
                    [
                        'floor_number' => $floor,
                        'sharing_type' => '2 Sharing AC',
                        'max_beds' => 2,
                        'is_ac' => true,
                        'status' => 'AVAILABLE',
                    ]
                );

                if (! $firstRoom) {
                    $firstRoom = $room;
                }

                // Create 2 Beds per room
                $bedA = Bed::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'bed_code' => 'Bed '.$floor.'A',
                    ],
                    [
                        'monthly_rent' => 6500.00,
                        'security_deposit' => 10000.00,
                        'status' => 'OCCUPIED',
                    ]
                );

                $bedB = Bed::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'bed_code' => 'Bed '.$floor.'B',
                    ],
                    [
                        'monthly_rent' => 6500.00,
                        'security_deposit' => 10000.00,
                        'status' => ($floor === 1 && $r === 1) ? 'OCCUPIED' : 'AVAILABLE',
                    ]
                );

                if (! $firstBed) {
                    $firstBed = $bedB;
                }
            }
        }

        // 6. Student Resident Profile
        $student = Student::firstOrCreate(
            ['app_reference' => 'REG-2026-8812'],
            [
                'user_id' => $studentUser->id,
                'branch_id' => $branch->id,
                'room_id' => $firstRoom ? $firstRoom->id : null,
                'bed_id' => $firstBed ? $firstBed->id : null,
                'full_name' => 'Rahul Sharma',
                'phone' => '+91 98765 43211',
                'email' => 'rahul.sharma@gmail.com',
                'aadhaar_number' => 'XXXX-XXXX-9912',
                'pan_number' => 'ABCDE1234F',
                'parent_name' => 'Sanjay Sharma',
                'parent_phone' => '+91 98250 11223',
                'emergency_contact' => '+91 98250 11223',
                'current_address' => '102, Shanti Nagar, SG Highway, Ahmedabad',
                'joining_date' => '2026-08-01',
                'kyc_status' => 'VERIFIED',
                'rent_status' => 'PAID',
                'deposit_status' => 'VERIFIED',
                'status' => 'APPROVED',
            ]
        );

        // 7. Student Room Allocation Record
        RoomAllocation::firstOrCreate(
            ['student_id' => $student->id, 'bed_id' => $firstBed->id],
            [
                'branch_id' => $branch->id,
                'room_id' => $firstRoom->id,
                'start_date' => '2026-08-01',
                'monthly_rent' => 6500.00,
                'security_deposit' => 10000.00,
                'status' => 'ACTIVE',
                'allocated_by' => $subAdmin->id,
            ]
        );

        // 8. Notifications
        Notification::firstOrCreate(
            ['user_id' => $studentUser->id, 'title' => 'Welcome to Naroda Branch'],
            [
                'body' => 'Your registration REG-2026-8812 has been approved and Bed 1B has been allocated.',
                'type' => 'REGISTRATION',
                'read_at' => now(),
            ]
        );

        // 9. Student KYC Documents
        StudentDocument::firstOrCreate(
            ['student_id' => $student->id, 'doc_type' => 'PROFILE_PHOTO'],
            ['file_path' => 'uploads/kyc/profile_doc.jpg', 'status' => 'VERIFIED', 'verified_by' => $subAdmin->id]
        );
        StudentDocument::firstOrCreate(
            ['student_id' => $student->id, 'doc_type' => 'AADHAAR_FRONT'],
            ['file_path' => 'uploads/kyc/aadhaar_front.jpg', 'status' => 'VERIFIED', 'verified_by' => $subAdmin->id]
        );
        StudentDocument::firstOrCreate(
            ['student_id' => $student->id, 'doc_type' => 'PAN_CARD'],
            ['file_path' => 'uploads/kyc/pan_card.jpg', 'status' => 'VERIFIED', 'verified_by' => $subAdmin->id]
        );

        // 10. Registration Requests Queue
        RegistrationRequest::firstOrCreate(
            ['app_reference' => 'REG-2026-8812'],
            ['branch_id' => $branch->id, 'student_id' => $student->id, 'status' => 'APPROVED', 'processed_by' => $subAdmin->id]
        );

        // 11. Payments Ledger
        $p1 = Payment::firstOrCreate(
            ['tx_reference' => 'PAY-2026-9912'],
            [
                'student_id' => $student->id,
                'branch_id' => $branch->id,
                'payment_type' => 'RENT',
                'amount' => 6500.00,
                'payment_mode' => 'UPI',
                'payment_date' => '2026-08-01',
                'status' => 'PAID',
                'paid_at' => '2026-08-01 10:30:00',
            ]
        );

        PaymentProof::firstOrCreate(
            ['payment_id' => $p1->id],
            ['utr_number' => 'UPI/619283746192', 'screenshot_path' => 'uploads/proofs/pay_9912.png', 'status' => 'VERIFIED', 'verified_by' => $subAdmin->id]
        );

        // 12. Electricity Sub-Meter Readings
        ElectricityReading::firstOrCreate(
            ['student_id' => $student->id, 'reading_month' => 'July 2026'],
            [
                'branch_id' => $branch->id,
                'room_id' => $firstRoom->id,
                'current_reading' => 14520,
                'previous_reading' => 14475,
                'units_consumed' => 45,
                'unit_rate' => 10.00,
                'total_amount' => 450.00,
                'meter_photo_path' => 'uploads/meter/july_2026.jpg',
                'status' => 'APPROVED',
                'audited_by' => $subAdmin->id,
            ]
        );

        // 13. Support Complaints
        Complaint::firstOrCreate(
            ['ticket_number' => 'TKT-2026-104'],
            [
                'branch_id' => $branch->id,
                'student_id' => $student->id,
                'room_id' => $firstRoom->id,
                'category' => 'PLUMBING',
                'subject' => 'Geyser Leakage in Washroom',
                'description' => 'Hot water geyser has a minor valve leak.',
                'priority' => 'MEDIUM',
                'status' => 'RESOLVED',
                'resolved_by' => $subAdmin->id,
            ]
        );

        // 14. Announcements
        Announcement::firstOrCreate(
            ['title' => 'August 2026 Rent Notice'],
            [
                'branch_id' => $branch->id,
                'content' => 'Monthly rent for August 2026 has been generated. Kindly clear dues before 5th August.',
                'category' => 'RENT REMINDER',
                'is_important' => true,
                'created_by' => $subAdmin->id,
            ]
        );

        // 15. System Settings
        SystemSetting::firstOrCreate(['key' => 'SYSTEM_NAME'], ['value' => 'Rudra Group PG Management System', 'group' => 'GENERAL']);
        SystemSetting::firstOrCreate(['key' => 'DEFAULT_ELECTRICITY_RATE'], ['value' => '10.00', 'group' => 'FINANCE']);
    }
}
