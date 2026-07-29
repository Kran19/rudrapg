enum ElectricityStatus {
  submitted,
  approved,
  rejected,
}

class ElectricityReadingModel {
  final String id;
  final String month;
  final double readingValue;
  final double unitsConsumed;
  final double totalAmount;
  final ElectricityStatus status;
  final DateTime dateSubmitted;

  ElectricityReadingModel({
    required this.id,
    required this.month,
    required this.readingValue,
    required this.unitsConsumed,
    required this.totalAmount,
    required this.status,
    required this.dateSubmitted,
  });
}
