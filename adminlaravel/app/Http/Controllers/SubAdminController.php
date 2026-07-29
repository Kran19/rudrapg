<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubAdminController extends Controller
{
    public function dashboard()
    {
        $branchInfo = [
            'name' => 'Naroda Branch',
            'code' => 'PG-NRD-01',
            'manager' => 'Suresh Patel',
            'total_rooms' => 40,
            'total_beds' => 100,
            'occupied_beds' => 84,
            'available_beds' => 16,
            'pending_verifications' => 3,
            'overdue_rents' => 5,
            'open_complaints' => 2,
        ];

        $pendingVerifications = [
            ['id' => 'BK-2026-0089', 'student_name' => 'Amit Trivedi', 'phone' => '+91 97123 44556', 'room' => 'Room 103 (Bed 3C)', 'rent' => '₹5,800', 'deposit' => '₹8,000', 'date' => '29 Jul 2026', 'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80', 'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80', 'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80'],
            ['id' => 'BK-2026-0091', 'student_name' => 'Vijay Chauhan', 'phone' => '+91 98981 22334', 'room' => 'Room 201 (Private)', 'rent' => '₹11,000', 'deposit' => '₹15,000', 'date' => '29 Jul 2026', 'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80', 'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80', 'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80'],
        ];

        return view('sub_admin.dashboard', compact('branchInfo', 'pendingVerifications'));
    }

    public function verifications()
    {
        $queue = [
            [
                'id' => 'BK-2026-0089',
                'student_name' => 'Amit Trivedi',
                'phone' => '+91 97123 44556',
                'aadhaar' => 'XXXX-XXXX-8822',
                'pan' => 'PQRTS8812M',
                'room_number' => '103',
                'bed_code' => 'Bed 3C',
                'sharing_type' => '3 Sharing (AC)',
                'rent' => '₹5,800',
                'deposit' => '₹8,000',
                'date' => '29 Jul 2026',
                'status' => 'Pending Verification',
                'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id' => 'BK-2026-0091',
                'student_name' => 'Vijay Chauhan',
                'phone' => '+91 98981 22334',
                'aadhaar' => 'XXXX-XXXX-1122',
                'pan' => 'MNOPS4433K',
                'room_number' => '201',
                'bed_code' => 'Bed 1A (Private)',
                'sharing_type' => 'Private Room (AC)',
                'rent' => '₹11,000',
                'deposit' => '₹15,000',
                'date' => '29 Jul 2026',
                'status' => 'Pending Verification',
                'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id' => 'BK-2026-0080',
                'student_name' => 'Rahul Sharma',
                'phone' => '+91 98765 43210',
                'aadhaar' => 'XXXX-XXXX-9912',
                'pan' => 'ABCDE1234F',
                'room_number' => '101',
                'bed_code' => 'Bed 1B',
                'sharing_type' => '2 Sharing (AC)',
                'rent' => '₹6,500',
                'deposit' => '₹10,000',
                'date' => '22 Jul 2026',
                'status' => 'Approved',
                'aadhaar_front' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'aadhaar_back' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=600&q=80',
                'payment_proof' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        return view('sub_admin.verifications', compact('queue'));
    }

    public function bedMap()
    {
        $rooms = [];
        for ($floor = 1; $floor <= 4; $floor++) {
            for ($r = 1; $r <= 10; $r++) {
                $roomNum = $floor * 100 + $r;
                $isAc = ($r % 2 == 1);
                $sharing = ($r == 1 || $r == 6) ? 'Private Room' : (($r % 3 == 0) ? '4 Sharing' : (($r % 2 == 0) ? '2 Sharing' : '3 Sharing'));
                $totalBeds = ($sharing == 'Private Room') ? 1 : (($sharing == '2 Sharing') ? 2 : (($sharing == '3 Sharing') ? 3 : 4));
                $available = ($roomNum % 3 == 0) ? 0 : (($roomNum % 2 == 0) ? 1 : $totalBeds);

                $beds = [];
                for ($b = 1; $b <= $totalBeds; $b++) {
                    $status = (($roomNum + $b) % 3 == 0) ? 'occupied' : ((($roomNum + $b) % 5 == 0) ? 'reserved' : 'available');
                    $beds[] = [
                        'code' => 'Bed ' . $roomNum . chr(64 + $b),
                        'status' => $status,
                        'student_name' => $status == 'occupied' ? 'Resident ' . ($roomNum * 10 + $b) : null,
                    ];
                }

                $rooms[] = [
                    'id' => $roomNum,
                    'room_number' => (string)$roomNum,
                    'floor' => $floor,
                    'sharing_type' => $sharing,
                    'is_ac' => $isAc,
                    'rent' => $isAc ? 6800 : 5800,
                    'total_beds' => $totalBeds,
                    'available_beds' => $available,
                    'beds' => $beds,
                ];
            }
        }

        return view('sub_admin.bed_map', compact('rooms'));
    }

    public function rentLedger()
    {
        $dues = [
            ['resident_id' => 'RES-8812', 'student_name' => 'Rahul Sharma', 'room' => '101 (Bed 1B)', 'rent' => '₹6,500', 'due_date' => '05 Aug 2026', 'status' => 'Paid (Proof Submitted)', 'payment_mode' => 'UPI Transfer', 'utr' => 'UPI/619283746192'],
            ['resident_id' => 'RES-8815', 'student_name' => 'Karan Patel', 'room' => '102 (Bed 2A)', 'rent' => '₹5,500', 'due_date' => '05 Aug 2026', 'status' => 'Pending Verification', 'payment_mode' => 'Cash', 'utr' => 'CASH-REC-104'],
            ['resident_id' => 'RES-8820', 'student_name' => 'Vikram Shah', 'room' => '104 (Bed 1A)', 'rent' => '₹6,800', 'due_date' => '05 Aug 2026', 'status' => 'Overdue', 'payment_mode' => '-', 'utr' => '-'],
            ['resident_id' => 'RES-8822', 'student_name' => 'Manish Dave', 'room' => '105 (Bed 3B)', 'rent' => '₹5,800', 'due_date' => '05 Aug 2026', 'status' => 'Paid', 'payment_mode' => 'UPI Transfer', 'utr' => 'UPI/887711223344'],
        ];

        return view('sub_admin.rent_ledger', compact('dues'));
    }

    public function electricityAudit()
    {
        $readings = [
            ['id' => 'E-2026-071', 'student' => 'Rahul Sharma', 'room' => '101', 'prev_reading' => 14475, 'curr_reading' => 14520, 'units' => 45, 'rate' => '₹10.00', 'total' => '₹450', 'photo_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80', 'date' => '28 Jul 2026', 'status' => 'Approved'],
            ['id' => 'E-2026-072', 'student' => 'Amit Trivedi', 'room' => '103', 'prev_reading' => 8810, 'curr_reading' => 8860, 'units' => 50, 'rate' => '₹10.00', 'total' => '₹500', 'photo_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80', 'date' => '29 Jul 2026', 'status' => 'Pending Audit'],
            ['id' => 'E-2026-073', 'student' => 'Vikram Shah', 'room' => '104', 'prev_reading' => 12100, 'curr_reading' => 12165, 'units' => 65, 'rate' => '₹10.00', 'total' => '₹650', 'photo_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80', 'date' => '29 Jul 2026', 'status' => 'Pending Audit'],
        ];

        return view('sub_admin.electricity_audit', compact('readings'));
    }

    public function complaints()
    {
        $tickets = [
            ['ticket' => 'TKT-9912', 'student' => 'Rahul Sharma', 'room' => '101', 'category' => 'Plumbing', 'title' => 'Bathroom Geyser Leaking', 'priority' => 'High', 'date' => '28 Jul 2026', 'status' => 'In Progress'],
            ['ticket' => 'TKT-9915', 'student' => 'Amit Trivedi', 'room' => '103', 'category' => 'Wi-Fi', 'title' => 'Slow Wi-Fi speed on Floor 1', 'priority' => 'Medium', 'date' => '29 Jul 2026', 'status' => 'Open'],
            ['ticket' => 'TKT-9904', 'student' => 'Karan Patel', 'room' => '102', 'category' => 'Cleaning', 'title' => 'Room Housekeeping Request', 'priority' => 'Low', 'date' => '25 Jul 2026', 'status' => 'Resolved'],
        ];

        return view('sub_admin.complaints', compact('tickets'));
    }
}
