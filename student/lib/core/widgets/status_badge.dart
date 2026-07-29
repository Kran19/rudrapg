import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';

class StatusBadge extends StatelessWidget {
  final String label;
  final Color color;
  final Color? textColor;
  final IconData? icon;

  const StatusBadge({
    super.key,
    required this.label,
    required this.color,
    this.textColor,
    this.icon,
  });

  factory StatusBadge.verified() => const StatusBadge(
        label: 'VERIFIED',
        color: AppColors.success,
        icon: Icons.check_circle_rounded,
      );

  factory StatusBadge.pending() => const StatusBadge(
        label: 'PENDING',
        color: AppColors.warning,
        icon: Icons.access_time_filled_rounded,
      );

  factory StatusBadge.rejected() => const StatusBadge(
        label: 'REJECTED',
        color: AppColors.error,
        icon: Icons.cancel_rounded,
      );

  factory StatusBadge.available() => const StatusBadge(
        label: 'AVAILABLE',
        color: AppColors.success,
        icon: Icons.event_available_rounded,
      );

  factory StatusBadge.occupied() => const StatusBadge(
        label: 'OCCUPIED',
        color: AppColors.error,
        icon: Icons.person_rounded,
      );

  factory StatusBadge.reserved() => const StatusBadge(
        label: 'RESERVED',
        color: AppColors.warning,
        icon: Icons.bookmark_rounded,
      );

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(AppSpacing.radiusBadge),
        border: Border.all(color: color.withValues(alpha: 0.3), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 12, color: color),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: AppTypography.badge.copyWith(
              color: textColor ?? color,
            ),
          ),
        ],
      ),
    );
  }
}
