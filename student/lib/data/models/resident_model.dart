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
  final String emergencyContactName;
  final String emergencyContactPhone;

  const ResidentModel({
    required this.id,
    required this.fullName,
    required this.phone,
    this.email = 'resident@gmail.com',
    required this.aadhaarNumber,
    required this.panNumber,
    this.parentName = 'Parent Name',
    this.parentPhone = '+91 98250 11223',
    this.emergencyContact = '+91 98250 11223',
    this.currentAddress = 'Ahmedabad, Gujarat',
    this.branchId = 'b1',
    required this.branchName,
    this.floorNumber = 1,
    required this.roomNumber,
    required this.bedCode,
    this.floor = '1',
    required this.sharingType,
    required this.monthlyRent,
    required this.securityDeposit,
    required this.joiningDate,
    this.kycStatus = 'Verified',
    this.rentStatus = 'Paid',
    this.depositStatus = 'Verified & Held',
    this.emergencyContactName = 'Guardian',
    this.emergencyContactPhone = '+91 98250 11223',
  });
}
