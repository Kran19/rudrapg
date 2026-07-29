<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'total_revenue' => '₹24,50,000',
            'occupancy_rate' => '84%',
            'active_branches' => 4,
            'total_students' => 320,
            'pending_approvals' => 14,
            'active_complaints' => 3,
        ];

        $recentActivities = [
            ['timestamp' => '10 mins ago', 'user' => 'Suresh Patel (Naroda)', 'action' => 'Verified Rent Payment', 'details' => '₹6,500 from Rahul Sharma (Room 101)'],
            ['timestamp' => '45 mins ago', 'user' => 'System Auto', 'action' => 'QR Code Branch Resolve', 'details' => 'New student scan at Satellite Branch'],
            ['timestamp' => '2 hours ago', 'user' => 'Amit Shah (Super Admin)', 'action' => 'Created New Branch', 'details' => 'Added SG Highway Branch (PG-SGH-04)'],
            ['timestamp' => '5 hours ago', 'user' => 'Ramesh Varma (Prahlad Nagar)', 'action' => 'Approved Electricity Bill', 'details' => '₹450 for Room 202'],
        ];

        return view('super_admin.dashboard', compact('metrics', 'recentActivities'));
    }

    public function branches()
    {
        $branches = [
            [
                'id' => 1,
                'code' => 'PG-NRD-01',
                'name' => 'Naroda Branch',
                'address' => '102, Main Highway Road, Opposite Science City Gate, Naroda',
                'city' => 'Ahmedabad',
                'manager' => 'Suresh Patel',
                'contact' => '+91 98765 43210',
                'rooms_count' => 40,
                'beds_count' => 100,
                'occupied_beds' => 84,
                'unit_rate' => '₹10.00 / unit',
                'qr_hash' => 'hash_naroda_88129931',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'code' => 'PG-SAT-02',
                'name' => 'Satellite Branch',
                'address' => '45, ISCON Cross Road, Near Star Bazaar, Satellite',
                'city' => 'Ahmedabad',
                'manager' => 'Kiran Mehta',
                'contact' => '+91 98765 88990',
                'rooms_count' => 30,
                'beds_count' => 75,
                'occupied_beds' => 62,
                'unit_rate' => '₹10.50 / unit',
                'qr_hash' => 'hash_satellite_99218271',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'code' => 'PG-PLN-03',
                'name' => 'Prahlad Nagar Branch',
                'address' => '12, Corporate Road, Opposite Venus Atlantis, Prahlad Nagar',
                'city' => 'Ahmedabad',
                'manager' => 'Ramesh Varma',
                'contact' => '+91 98250 11223',
                'rooms_count' => 35,
                'beds_count' => 90,
                'occupied_beds' => 78,
                'unit_rate' => '₹11.00 / unit',
                'qr_hash' => 'hash_prahladnagar_11209384',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'code' => 'PG-SGH-04',
                'name' => 'SG Highway Branch',
                'address' => '88, Near YMCA Club, SG Highway',
                'city' => 'Ahmedabad',
                'manager' => 'Vikram Singh',
                'contact' => '+91 98980 44556',
                'rooms_count' => 50,
                'beds_count' => 120,
                'occupied_beds' => 96,
                'unit_rate' => '₹10.00 / unit',
                'qr_hash' => 'hash_sghighway_55443322',
                'status' => 'Active',
            ],
        ];

        return view('super_admin.branches', compact('branches'));
    }

    public function subAdmins()
    {
        $subAdmins = [
            ['id' => 101, 'name' => 'Suresh Patel', 'email' => 'suresh.naroda@rudrapg.com', 'phone' => '+91 98765 12345', 'assigned_branches' => ['Naroda Branch'], 'status' => 'Active', 'created_at' => '12 Jan 2026'],
            ['id' => 102, 'name' => 'Kiran Mehta', 'email' => 'kiran.sat@rudrapg.com', 'phone' => '+91 98765 88990', 'assigned_branches' => ['Satellite Branch'], 'status' => 'Active', 'created_at' => '05 Feb 2026'],
            ['id' => 103, 'name' => 'Ramesh Varma', 'email' => 'ramesh.pln@rudrapg.com', 'phone' => '+91 98250 11223', 'assigned_branches' => ['Prahlad Nagar Branch', 'SG Highway Branch'], 'status' => 'Active', 'created_at' => '20 Feb 2026'],
            ['id' => 104, 'name' => 'Vikram Singh', 'email' => 'vikram.sgh@rudrapg.com', 'phone' => '+91 98980 44556', 'assigned_branches' => ['SG Highway Branch'], 'status' => 'Active', 'created_at' => '01 Mar 2026'],
        ];

        return view('super_admin.sub_admins', compact('subAdmins'));
    }

    public function roomsMaster()
    {
        // 40 Rooms dataset for Naroda Branch
        $rooms = [];
        for ($floor = 1; $floor <= 4; $floor++) {
            for ($r = 1; $r <= 10; $r++) {
                $roomNum = $floor * 100 + $r;
                $isAc = ($r % 2 == 1);
                $sharing = ($r == 1 || $r == 6) ? 'Private Room' : (($r % 3 == 0) ? '4 Sharing' : (($r % 2 == 0) ? '2 Sharing' : '3 Sharing'));
                $totalBeds = ($sharing == 'Private Room') ? 1 : (($sharing == '2 Sharing') ? 2 : (($sharing == '3 Sharing') ? 3 : 4));
                $available = ($roomNum % 3 == 0) ? 0 : (($roomNum % 2 == 0) ? 1 : $totalBeds);

                $rooms[] = [
                    'id' => $roomNum,
                    'room_number' => (string)$roomNum,
                    'floor' => $floor,
                    'sharing_type' => $sharing,
                    'is_ac' => $isAc,
                    'rent' => $isAc ? 6800 : 5800,
                    'deposit' => $isAc ? 10000 : 8000,
                    'total_beds' => $totalBeds,
                    'available_beds' => $available,
                    'occupied_beds' => $totalBeds - $available,
                    'status' => $available == 0 ? 'Full' : 'Available',
                ];
            }
        }

        return view('super_admin.rooms_master', compact('rooms'));
    }

    public function students()
    {
        $students = [
            ['id' => 'STU-1001', 'name' => 'Rahul Sharma', 'phone' => '+91 98765 43210', 'email' => 'rahul.sharma@gmail.com', 'branch' => 'Naroda Branch', 'room_bed' => 'Room 101 (Bed 1B)', 'aadhaar' => 'XXXX-XXXX-9912', 'pan' => 'ABCDE1234F', 'joining' => '01 Aug 2026', 'kyc_status' => 'Verified', 'rent_status' => 'Paid'],
            ['id' => 'STU-1002', 'name' => 'Priya Verma', 'phone' => '+91 98250 99887', 'email' => 'priya.v@gmail.com', 'branch' => 'Satellite Branch', 'room_bed' => 'Room 202 (Bed 2A)', 'aadhaar' => 'XXXX-XXXX-4411', 'pan' => 'XYZPK9921L', 'joining' => '15 Jul 2026', 'kyc_status' => 'Verified', 'rent_status' => 'Paid'],
            ['id' => 'STU-1003', 'name' => 'Amit Trivedi', 'phone' => '+91 97123 44556', 'email' => 'amit.t@gmail.com', 'branch' => 'Naroda Branch', 'room_bed' => 'Room 103 (Bed 3C)', 'aadhaar' => 'XXXX-XXXX-8822', 'pan' => 'PQRTS8812M', 'joining' => '20 Jul 2026', 'kyc_status' => 'Pending', 'rent_status' => 'Pending'],
            ['id' => 'STU-1004', 'name' => 'Karan Patel', 'phone' => '+91 99000 11223', 'email' => 'karan.p@gmail.com', 'branch' => 'Prahlad Nagar Branch', 'room_bed' => 'Room 301 (Bed 1A)', 'aadhaar' => 'XXXX-XXXX-5566', 'pan' => 'LMNOP3344K', 'joining' => '01 Jun 2026', 'kyc_status' => 'Verified', 'rent_status' => 'Overdue'],
            ['id' => 'STU-1005', 'name' => 'Sneha Gupta', 'phone' => '+91 98777 66554', 'email' => 'sneha.g@gmail.com', 'branch' => 'SG Highway Branch', 'room_bed' => 'Room 402 (Bed 2B)', 'aadhaar' => 'XXXX-XXXX-1199', 'pan' => 'RSTUV5566N', 'joining' => '05 Aug 2026', 'kyc_status' => 'Verified', 'rent_status' => 'Paid'],
        ];

        return view('super_admin.students', compact('students'));
    }

    public function finance()
    {
        $financeSummary = [
            'total_collections_this_month' => '₹18,40,000',
            'pending_rent_dues' => '₹1,25,000',
            'total_security_deposits_held' => '₹32,50,000',
            'electricity_collections' => '₹84,500',
        ];

        $transactions = [
            ['tx_id' => 'PAY-2026-9912', 'student' => 'Rahul Sharma', 'branch' => 'Naroda Branch', 'type' => 'Rent', 'amount' => '₹6,500', 'mode' => 'UPI Transfer', 'ref' => 'UPI/619283746192', 'date' => '28 Jul 2026', 'status' => 'Verified'],
            ['tx_id' => 'PAY-2026-8811', 'student' => 'Rahul Sharma', 'branch' => 'Naroda Branch', 'type' => 'Security Deposit', 'amount' => '₹10,000', 'mode' => 'Bank Transfer', 'ref' => 'UTR-N992817263', 'date' => '22 Jul 2026', 'status' => 'Verified'],
            ['tx_id' => 'PAY-2026-1042', 'student' => 'Amit Trivedi', 'branch' => 'Naroda Branch', 'type' => 'Electricity Bill', 'amount' => '₹450', 'mode' => 'Cash', 'ref' => 'CASH-REC-102', 'date' => '20 Jul 2026', 'status' => 'Verified'],
            ['tx_id' => 'PAY-2026-1190', 'student' => 'Karan Patel', 'branch' => 'Prahlad Nagar Branch', 'type' => 'Rent', 'amount' => '₹6,000', 'mode' => 'UPI Transfer', 'ref' => 'UPI/998877665544', 'date' => '27 Jul 2026', 'status' => 'Pending Verification'],
        ];

        return view('super_admin.finance', compact('financeSummary', 'transactions'));
    }

    public function settings()
    {
        $auditLogs = [
            ['id' => 8812, 'timestamp' => '2026-07-29 23:45:10', 'user' => 'Suresh Patel (Sub Admin)', 'action' => 'Verified Payment Proof', 'module' => 'Payments', 'ip' => '103.22.44.11'],
            ['id' => 8811, 'timestamp' => '2026-07-29 21:12:04', 'user' => 'Amit Shah (Super Admin)', 'action' => 'Updated Electricity Tariff to ₹10.00', 'module' => 'Branches', 'ip' => '114.31.88.99'],
            ['id' => 8810, 'timestamp' => '2026-07-29 18:05:40', 'user' => 'System API', 'action' => 'Generated QR Hash for PG-NRD-01', 'module' => 'System', 'ip' => '127.0.0.1'],
            ['id' => 8809, 'timestamp' => '2026-07-29 15:30:22', 'user' => 'Kiran Mehta (Sub Admin)', 'action' => 'Approved Booking BK-2026-0089', 'module' => 'Bookings', 'ip' => '49.36.120.44'],
        ];

        return view('super_admin.settings', compact('auditLogs'));
    }
}
