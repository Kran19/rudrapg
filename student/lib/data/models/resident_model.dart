class ResidentModel {
  final String id;
  final String fullName;
  final String phone;
  final String email;
  final String aadhaarNumber;
  final String panNumber;
  final String parentName;
  final String parentPhone;
  final String emergencyContact;
  final String currentAddress;
  final String branchId;
  final String branchName;
  final int floorNumber;
  final String roomNumber;
  final String bedCode;
  final String floor;
  final String sharingType;
  final double monthlyRent;
  final double securityDeposit;
  final String joiningDate;
  final String kycStatus;
  final String rentStatus;
  final String depositStatus;
  final String status;
  final String emergencyContactName;
  final String emergencyContactPhone;

  const ResidentModel({
    required this.id,
    required this.fullName,
    required this.phone,
    this.email = '',
    required this.aadhaarNumber,
    required this.panNumber,
    this.parentName = '',
    this.parentPhone = '',
    this.emergencyContact = '',
    this.currentAddress = '',
    this.branchId = '',
    required this.branchName,
    this.floorNumber = 1,
    required this.roomNumber,
    required this.bedCode,
    this.floor = '1',
    required this.sharingType,
    required this.monthlyRent,
    required this.securityDeposit,
    required this.joiningDate,
    this.kycStatus = 'Pending',
    this.rentStatus = 'Due',
    this.depositStatus = 'Pending',
    this.status = 'PENDING_APPROVAL',
    this.emergencyContactName = '',
    this.emergencyContactPhone = '',
  });

  bool get isRoomAssigned =>
      roomNumber.isNotEmpty &&
      roomNumber != 'N/A' &&
      roomNumber != 'Pending Allocation' &&
      roomNumber != 'Pending' &&
      bedCode != 'N/A' &&
      bedCode != 'Pending' &&
      monthlyRent > 0;
}
