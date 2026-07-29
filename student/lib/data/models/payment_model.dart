enum PaymentStatus {
  verified,
  pending,
  rejected,
}

enum PaymentType {
  rent,
  deposit,
  electricity,
}

class PaymentModel {
  final String id;
  final String transactionId;
  final String title;
  final double amount;
  final PaymentType type;
  final PaymentStatus status;
  final DateTime date;
  final String paymentMode;
  final String? utrNumber;
  final String? receiptUrl;

  PaymentModel({
    required this.id,
    required this.transactionId,
    required this.title,
    required this.amount,
    required this.type,
    required this.status,
    required this.date,
    required this.paymentMode,
    this.utrNumber,
    this.receiptUrl,
  });
}
