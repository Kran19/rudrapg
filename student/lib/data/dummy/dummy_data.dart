import '../models/branch_model.dart';
import '../models/room_model.dart';
import '../models/bed_model.dart';
import '../models/payment_model.dart';
import '../models/notification_model.dart';
import '../models/electricity_model.dart';
import '../models/resident_model.dart';

class DummyData {
  DummyData._();

  static final BranchModel activeBranch = BranchModel(
    id: 'b-01',
    code: 'PG-NRD-01',
    name: 'Rudra Boys PG - Naroda Branch',
    address: '102, Main Highway Road, Opposite Science City Gate, Naroda',
    city: 'Ahmedabad, Gujarat',
    contactNumber: '+91 98765 43210',
    managerName: 'Suresh Patel',
    managerPhone: '+91 98765 12345',
    imageUrl: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1000&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1000&q=80',
      'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=1000&q=80',
      'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1000&q=80',
      'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1000&q=80',
    ],
    amenities: [
      'High-Speed Wi-Fi',
      'RO Water Purifier',
      'CCTV Surveillance',
      'Daily Housekeeping',
      'Laundry Machine',
      '24/7 Power Backup',
      'Geyser in Bathroom',
      'Study Desk & Chair',
    ],
    rules: [
      'Visitors allowed only in main reception till 8:00 PM.',
      'Maintain quiet hours after 10:30 PM.',
      'Electricity bills to be paid before 5th of every month.',
      'No smoking or alcoholic beverages permitted inside premises.',
    ],
  );

  static List<RoomModel> generate40Rooms() {
    final List<RoomModel> rooms = [];
    int idCounter = 1;

    for (int floor = 1; floor <= 4; floor++) {
      for (int r = 1; r <= 10; r++) {
        final roomNum = floor * 100 + r;

        // Alternate sharing types: 1=Solo, 2,3,4=Sharing
        String sharing;
        int bedCount;
        double rent;
        bool isAc = (r % 2 == 1); // Alternating AC / Non-AC

        if (r == 1 || r == 6) {
          sharing = 'Private Room';
          bedCount = 1;
          rent = isAc ? 11000 : 9500;
        } else if (r % 3 == 0) {
          sharing = '4 Sharing';
          bedCount = 4;
          rent = isAc ? 5200 : 4500;
        } else if (r % 2 == 0) {
          sharing = '2 Sharing';
          bedCount = 2;
          rent = isAc ? 6800 : 6000;
        } else {
          sharing = '3 Sharing';
          bedCount = 3;
          rent = isAc ? 5800 : 5000;
        }

        // Generate Beds with varying occupancy states
        final List<BedModel> beds = [];
        for (int b = 1; b <= bedCount; b++) {
          BedStatus status;
          if ((roomNum + b) % 3 == 0) {
            status = BedStatus.occupied;
          } else if ((roomNum + b) % 5 == 0) {
            status = BedStatus.reserved;
          } else {
            status = BedStatus.available;
          }

          beds.add(
            BedModel(
              id: 'bed-$idCounter-$b',
              code: 'Bed $roomNum${String.fromCharCode(64 + b)}',
              roomId: 'room-$idCounter',
              status: status,
            ),
          );
        }

        rooms.add(
          RoomModel(
            id: 'room-$idCounter',
            roomNumber: '$roomNum',
            branchId: 'b-01',
            floor: floor,
            sharingType: sharing,
            monthlyRent: rent,
            securityDeposit: rent * 1.5,
            isAc: isAc,
            hasAttachedWashroom: (r <= 7),
            roomSize: '${180 + (bedCount * 40)} sq ft',
            furniture: ['Individual Bed', 'Study Desk', 'Cupboard'],
            images: [
              isAc
                  ? 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=800&q=80'
                  : 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80',
            ],
            beds: beds,
          ),
        );
        idCounter++;
      }
    }
    return rooms;
  }

  static final List<RoomModel> sampleRooms = generate40Rooms();

  static final ResidentModel sampleResident = ResidentModel(
    id: 'res-8812',
    fullName: 'Rahul Sharma',
    phone: '+91 98765 43210',
    aadhaarNumber: 'XXXX-XXXX-9912',
    panNumber: 'ABCDE1234F',
    branchName: 'Naroda Branch',
    roomNumber: '101',
    bedCode: 'Bed 1B',
    floor: '1st Floor',
    sharingType: '2 Sharing (AC)',
    monthlyRent: 6500.0,
    securityDeposit: 10000.0,
    joiningDate: '01 Aug 2026',
    emergencyContactName: 'Mahesh Sharma (Father)',
    emergencyContactPhone: '+91 98250 11223',
  );

  static final List<PaymentModel> paymentHistory = [
    PaymentModel(
      id: 'p-01',
      transactionId: 'PAY-2026-9912',
      title: 'July 2026 Rent Payment',
      amount: 6500.0,
      type: PaymentType.rent,
      status: PaymentStatus.verified,
      date: DateTime.now().subtract(const Duration(days: 28)),
      paymentMode: 'UPI Transfer',
      utrNumber: 'UPI/619283746192',
    ),
    PaymentModel(
      id: 'p-02',
      transactionId: 'PAY-2026-8811',
      title: 'Security Deposit',
      amount: 10000.0,
      type: PaymentType.deposit,
      status: PaymentStatus.verified,
      date: DateTime.now().subtract(const Duration(days: 35)),
      paymentMode: 'Bank Transfer',
      utrNumber: 'UTR-N992817263',
    ),
    PaymentModel(
      id: 'p-03',
      transactionId: 'PAY-2026-1042',
      title: 'June Electricity Bill',
      amount: 450.0,
      type: PaymentType.electricity,
      status: PaymentStatus.verified,
      date: DateTime.now().subtract(const Duration(days: 20)),
      paymentMode: 'Cash',
    ),
    PaymentModel(
      id: 'p-04',
      transactionId: 'PAY-2026-1190',
      title: 'August 2026 Rent Payment',
      amount: 6500.0,
      type: PaymentType.rent,
      status: PaymentStatus.pending,
      date: DateTime.now().subtract(const Duration(days: 2)),
      paymentMode: 'UPI Transfer',
      utrNumber: 'UPI/998877665544',
    ),
  ];

  static final List<NotificationModel> sampleNotifications = [
    NotificationModel(
      id: 'n-01',
      title: 'Rent Reminder for August',
      message: 'Your monthly rent of ₹6,500 for Room 101 is due on 5th Aug. Please submit payment proof.',
      timestamp: DateTime.now().subtract(const Duration(hours: 3)),
      type: NotificationType.rentReminder,
    ),
    NotificationModel(
      id: 'n-02',
      title: 'Electricity Meter Submission Open',
      message: 'Kindly submit your current meter reading and photo before the 3rd of this month.',
      timestamp: DateTime.now().subtract(const Duration(days: 1)),
      type: NotificationType.electricity,
    ),
    NotificationModel(
      id: 'n-03',
      title: 'Payment Verified',
      message: 'Your July rent payment of ₹6,500 has been verified by Suresh Patel (Sub Admin).',
      timestamp: DateTime.now().subtract(const Duration(days: 5)),
      type: NotificationType.paymentVerified,
      isRead: true,
    ),
    NotificationModel(
      id: 'n-04',
      title: 'Water Tank Cleaning Notice',
      message: 'Main overhead water tanks will be cleaned tomorrow between 10 AM and 1 PM.',
      timestamp: DateTime.now().subtract(const Duration(days: 7)),
      type: NotificationType.announcement,
      isRead: true,
    ),
  ];

  static final List<ElectricityReadingModel> electricityHistory = [
    ElectricityReadingModel(
      id: 'e-01',
      month: 'July 2026',
      readingValue: 14520.0,
      unitsConsumed: 45.0,
      totalAmount: 450.0,
      status: ElectricityStatus.approved,
      dateSubmitted: DateTime.now().subtract(const Duration(days: 25)),
    ),
    ElectricityReadingModel(
      id: 'e-02',
      month: 'June 2026',
      readingValue: 14475.0,
      unitsConsumed: 40.0,
      totalAmount: 400.0,
      status: ElectricityStatus.approved,
      dateSubmitted: DateTime.now().subtract(const Duration(days: 55)),
    ),
  ];
}
