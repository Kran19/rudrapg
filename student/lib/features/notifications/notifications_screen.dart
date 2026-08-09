import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../home/data/student_repository.dart';

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final noticesAsync = ref.watch(noticeHistoryProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Branch Notice Board'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: noticesAsync.when(
          data: (notices) {
            if (notices.isEmpty) {
              return const Center(child: Text('No notices posted yet.'));
            }
            return ListView.separated(
              padding: const EdgeInsets.all(AppSpacing.lg),
              itemCount: notices.length,
              separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
              itemBuilder: (context, index) {
                final notice = notices[index];
                final bool isImportant = (notice['is_important'] ?? notice['isImportant'] ?? false) as bool;
                final category = notice['category'] ?? 'ANNOUNCEMENT';
                final title = notice['title'] ?? '';
                final content = notice['content'] ?? '';
                final date = notice['created_at'] ?? notice['date'] ?? '';

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
                              color: _getCategoryColor(category).withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Text(
                              category,
                              style: AppTypography.badge.copyWith(
                                color: _getCategoryColor(category),
                              ),
                            ),
                          ),
                          Text(
                            date,
                            style: AppTypography.caption.copyWith(color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.md),
                      Text(
                        title,
                        style: AppTypography.titleMedium,
                      ),
                      const SizedBox(height: 6),
                      Text(
                        content,
                        style: AppTypography.bodySmall.copyWith(
                          color: AppColors.textSecondary,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                );
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (err, stack) => Center(child: Text('Error loading notices: $err')),
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
