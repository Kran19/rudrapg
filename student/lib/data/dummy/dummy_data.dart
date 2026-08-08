import '../../data/models/branch_model.dart';
import '../../data/models/resident_model.dart';

class DummyData {
  static const BranchModel activeBranch = BranchModel(
    id: 'b1',
    code: 'PG-NRD-01',
    name: 'Naroda Branch',
    address: '102, Main Highway Road, Opposite Science City Gate, Naroda, Ahmedabad',
    city: 'Ahmedabad',
    managerName: 'Suresh Patel',
    managerPhone: '+91 98765 43210',
    managerEmail: 'suresh.naroda@rudrapg.com',
    electricityUnitRate: 10.0,
    totalRooms: 40,
    totalBeds: 100,
    occupiedBeds: 84,
    qrCodeHash: 'hash_naroda_88129931',
    isAcAvailable: true,
    startingPrice: 5800.0,
  );

  static const ResidentModel sampleResident = ResidentModel(
    id: 'STU-1001',
    fullName: 'Rahul Sharma',
    phone: '+91 98765 43210',
    email: 'rahul.sharma@gmail.com',
    aadhaarNumber: 'XXXX-XXXX-9912',
    panNumber: 'ABCDE1234F',
    parentName: 'Sanjay Sharma',
    parentPhone: '+91 98250 11223',
    emergencyContact: '+91 98250 11223',
    currentAddress: '102, Shanti Nagar, SG Highway, Ahmedabad',
    branchId: 'b1',
    branchName: 'Naroda Branch',
    floorNumber: 1,
    roomNumber: '101',
    bedCode: 'Bed 1B',
    sharingType: '2 Sharing (AC)',
    monthlyRent: 6500.0,
    securityDeposit: 10000.0,
    joiningDate: '01 Aug 2026',
    kycStatus: 'Verified',
    rentStatus: 'Paid',
    depositStatus: 'Verified & Held',
  );

  static final List<Map<String, dynamic>> notices = [
    {
      'id': 'n1',
      'title': 'August 2026 Rent Notice',
      'content': 'Monthly rent for August 2026 has been generated. Kindly clear dues before 5th August to avoid late charges.',
      'category': 'RENT REMINDER',
      'date': '01 Aug 2026',
      'isImportant': true,
    },
    {
      'id': 'n2',
      'title': 'Overhead Water Tank Cleaning',
      'content': 'Overhead water tank maintenance is scheduled for Sunday from 10:00 AM to 1:00 PM. Water supply will be temporarily paused.',
      'category': 'WATER SHUTDOWN',
      'date': '29 Jul 2026',
      'isImportant': false,
    },
    {
      'id': 'n3',
      'title': 'Monthly Electricity Meter Reading',
      'content': 'Please submit your sub-meter reading photo before 30th July via the Electricity section in your resident app.',
      'category': 'ELECTRICITY MAINTENANCE',
      'date': '25 Jul 2026',
      'isImportant': false,
    },
    {
      'id': 'n4',
      'title': 'High-Speed Wi-Fi Upgrade',
      'content': 'Wi-Fi routers on Floor 1 & Floor 2 have been upgraded to 300 Mbps high-speed fiber connection.',
      'category': 'GENERAL ANNOUNCEMENT',
      'date': '20 Jul 2026',
      'isImportant': false,
    },
  ];

  static final List<Map<String, dynamic>> paymentHistory = [
    {
      'txId': 'PAY-2026-9912',
      'type': 'Monthly Rent (August)',
      'amount': 6500.0,
      'date': '01 Aug 2026',
      'mode': 'UPI Transfer',
      'ref': 'UPI/619283746192',
      'status': 'Paid',
    },
    {
      'txId': 'PAY-2026-8811',
      'type': 'Security Deposit',
      'amount': 10000.0,
      'date': '22 Jul 2026',
      'mode': 'Bank Transfer',
      'ref': 'UTR-N992817263',
      'status': 'Verified & Held',
    },
    {
      'txId': 'PAY-2026-1042',
      'type': 'Electricity Bill (July)',
      'amount': 450.0,
      'date': '20 Jul 2026',
      'mode': 'Cash at Desk',
      'ref': 'CASH-REC-102',
      'status': 'Paid',
    },
  ];

  static final Map<String, dynamic> electricityData = {
    'currReading': 14520,
    'prevReading': 14475,
    'unitsConsumed': 45,
    'unitRate': 10.0,
    'totalAmount': 450.0,
    'lastSubmissionDate': '28 Jul 2026',
    'status': 'Approved',
    'history': [
      {'month': 'July 2026', 'reading': 14520, 'units': 45, 'amount': 450.0, 'status': 'Approved'},
      {'month': 'June 2026', 'reading': 14475, 'units': 50, 'amount': 500.0, 'status': 'Approved'},
      {'month': 'May 2026', 'reading': 14425, 'units': 42, 'amount': 420.0, 'status': 'Approved'},
    ],
  };

  static final List<Map<String, String>> faqs = [
    {
      'question': 'How do I pay monthly rent?',
      'answer': 'You can pay rent via UPI transfer using the branch QR code in the Payments section, or hand over cash to the Sub Admin at the branch office desk.'
    },
    {
      'question': 'How is electricity bill calculated?',
      'answer': 'Each room has a dedicated sub-meter. Your monthly consumption (Current Reading - Previous Reading) is multiplied by your branch tariff rate.'
    },
    {
      'question': 'Can I request a room or bed transfer?',
      'answer': 'Room transfers require branch manager approval based on bed availability. Raise a request in Support or visit the branch office desk.'
    },
  ];
}
