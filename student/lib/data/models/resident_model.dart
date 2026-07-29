class ResidentModel {
  final String id;
  final String fullName;
  final String phone;
  final String aadhaarNumber;
  final String panNumber;
  final String branchName;
  final String roomNumber;
  final String bedCode;
  final String floor;
  final String sharingType;
  final double monthlyRent;
  final double securityDeposit;
  final String joiningDate;
  final String emergencyContactName;
  final String emergencyContactPhone;

  ResidentModel({
    required this.id,
    required this.fullName,
    required this.phone,
    required this.aadhaarNumber,
    required this.panNumber,
    required this.branchName,
    required this.roomNumber,
    required this.bedCode,
    required this.floor,
    required this.sharingType,
    required this.monthlyRent,
    required this.securityDeposit,
    required this.joiningDate,
    required this.emergencyContactName,
    required this.emergencyContactPhone,
  });
}
