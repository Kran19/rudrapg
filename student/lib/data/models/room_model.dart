import 'bed_model.dart';

class RoomModel {
  final String id;
  final String roomNumber;
  final String branchId;
  final int floor;
  final String sharingType; // e.g., '2 Sharing', '3 Sharing', 'Private Room', '4 Sharing'
  final double monthlyRent;
  final double securityDeposit;
  final bool isAc;
  final bool hasAttachedWashroom;
  final String roomSize;
  final List<String> furniture;
  final List<String> images;
  final List<BedModel> beds;

  RoomModel({
    required this.id,
    required this.roomNumber,
    required this.branchId,
    required this.floor,
    required this.sharingType,
    required this.monthlyRent,
    required this.securityDeposit,
    required this.isAc,
    required this.hasAttachedWashroom,
    required this.roomSize,
    required this.furniture,
    required this.images,
    required this.beds,
  });

  int get availableBedsCount => beds.where((b) => b.status == BedStatus.available).length;
  int get occupiedBedsCount => beds.where((b) => b.status == BedStatus.occupied).length;
  int get reservedBedsCount => beds.where((b) => b.status == BedStatus.reserved).length;
  int get totalBedsCount => beds.length;
}
