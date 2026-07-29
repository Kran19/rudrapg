import 'package:flutter/material.dart';
import '../../data/models/notification_model.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';
import 'custom_card.dart';

class NotificationCard extends StatelessWidget {
  final NotificationModel notification;

  const NotificationCard({
    super.key,
    required this.notification,
  });

  @override
  Widget build(BuildContext context) {
    IconData icon;
    Color iconColor;

    switch (notification.type) {
      case NotificationType.rentReminder:
        icon = Icons.alarm_rounded;
        iconColor = AppColors.warning;
        break;
      case NotificationType.paymentVerified:
        icon = Icons.check_circle_rounded;
        iconColor = AppColors.success;
        break;
      case NotificationType.electricity:
        icon = Icons.bolt_rounded;
        iconColor = AppColors.secondary;
        break;
      case NotificationType.announcement:
        icon = Icons.campaign_rounded;
        iconColor = AppColors.accent;
        break;
      case NotificationType.maintenance:
        icon = Icons.build_rounded;
        iconColor = AppColors.error;
        break;
    }

    return CustomCard(
      backgroundColor: notification.isRead ? AppColors.card : AppColors.secondary.withValues(alpha: 0.04),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        notification.title,
                        style: AppTypography.titleSmall.copyWith(
                          fontWeight: notification.isRead ? FontWeight.w500 : FontWeight.bold,
                        ),
                      ),
                    ),
                    Text(
                      _formatTime(notification.timestamp),
                      style: AppTypography.caption,
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  notification.message,
                  style: AppTypography.bodyMedium,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _formatTime(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inHours < 1) {
      return '${diff.inMinutes}m ago';
    } else if (diff.inHours < 24) {
      return '${diff.inHours}h ago';
    } else {
      return '${diff.inDays}d ago';
    }
  }
}
