class BranchModel {
  final String id;
  final String code;
  final String name;
  final String address;
  final String city;
  final String contactNumber;
  final String managerName;
  final String managerPhone;
  final String managerEmail;
  final double electricityUnitRate;
  final int totalRooms;
  final int totalBeds;
  final int occupiedBeds;
  final String qrCodeHash;
  final bool isAcAvailable;
  final double startingPrice;
  final String imageUrl;
  final List<String> galleryImages;
  final List<String> amenities;
  final List<String> rules;

  const BranchModel({
    required this.id,
    required this.code,
    required this.name,
    required this.address,
    required this.city,
    this.contactNumber = '+91 98765 43210',
    required this.managerName,
    required this.managerPhone,
    this.managerEmail = 'manager@rudrapg.com',
    this.electricityUnitRate = 10.0,
    this.totalRooms = 40,
    this.totalBeds = 100,
    this.occupiedBeds = 84,
    this.qrCodeHash = 'hash_branch_qr',
    this.isAcAvailable = true,
    this.startingPrice = 5800.0,
    this.imageUrl = 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5',
    this.galleryImages = const [],
    this.amenities = const ['Wi-Fi', 'AC', 'Housekeeping', 'Food'],
    this.rules = const ['No Smoking', 'Entry till 10:30 PM'],
  });
}
