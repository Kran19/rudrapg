import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../../data/dummy/dummy_data.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final notices = DummyData.notices;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Branch Notice Board'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: ListView.separated(
          padding: const EdgeInsets.all(AppSpacing.lg),
          itemCount: notices.length,
          separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
          itemBuilder: (context, index) {
            final notice = notices[index];
            final bool isImportant = notice['isImportant'] as bool;

            return CustomCard(
              backgroundColor: isImportant ? AppColors.warning.withValues(alpha: 0.05) : Colors.white,
              border: Border.all(
                color: isImportant ? AppColors.warning.withValues(alpha: 0.3) : AppColors.divider,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: _getCategoryColor(notice['category']).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Text(
                          notice['category'],
                          style: AppTypography.badge.copyWith(
                            color: _getCategoryColor(notice['category']),
                          ),
                        ),
                      ),
                      Text(
                        notice['date'],
                        style: AppTypography.caption.copyWith(color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    notice['title'],
                    style: AppTypography.titleMedium,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    notice['content'],
                    style: AppTypography.bodySmall.copyWith(
                      color: AppColors.textSecondary,
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Color _getCategoryColor(String category) {
    switch (category) {
      case 'RENT REMINDER':
        return AppColors.warning;
      case 'WATER SHUTDOWN':
        return AppColors.secondary;
      case 'ELECTRICITY MAINTENANCE':
        return AppColors.accent;
      default:
        return AppColors.primary;
    }
  }
}
