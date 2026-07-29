enum BedStatus {
  available,
  occupied,
  reserved,
  selected,
}

class BedModel {
  final String id;
  final String code; // e.g. Bed 1A
  final String roomId;
  BedStatus status;

  BedModel({
    required this.id,
    required this.code,
    required this.roomId,
    required this.status,
  });
}
