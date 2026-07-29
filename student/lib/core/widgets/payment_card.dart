import 'package:flutter/material.dart';
import '../../data/models/payment_model.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';
import 'custom_card.dart';
import 'status_badge.dart';

class PaymentCard extends StatelessWidget {
  final PaymentModel payment;

  const PaymentCard({
    super.key,
    required this.payment,
  });

  @override
  Widget build(BuildContext context) {
    StatusBadge badge;
    switch (payment.status) {
      case PaymentStatus.verified:
        badge = StatusBadge.verified();
        break;
      case PaymentStatus.pending:
        badge = StatusBadge.pending();
        break;
      case PaymentStatus.rejected:
        badge = StatusBadge.rejected();
        break;
    }

    return CustomCard(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.secondary.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.receipt_long_rounded,
              color: AppColors.secondary,
              size: 24,
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  payment.title,
                  style: AppTypography.titleSmall,
                ),
                const SizedBox(height: 2),
                Text(
                  '${payment.paymentMode} • ${payment.transactionId}',
                  style: AppTypography.bodySmall,
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '₹${payment.amount.toInt()}',
                style: AppTypography.titleMedium.copyWith(color: AppColors.primary),
              ),
              const SizedBox(height: 4),
              badge,
            ],
          ),
        ],
      ),
    );
  }
}
