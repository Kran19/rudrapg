import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/notification_card.dart';
import '../../data/dummy/dummy_data.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final notifications = DummyData.sampleNotifications;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Notices & Alerts', showBackButton: false),
      body: ListView.separated(
        padding: const EdgeInsets.all(AppSpacing.lg),
        itemCount: notifications.length,
        separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
        itemBuilder: (context, index) {
          return NotificationCard(notification: notifications[index]);
        },
      ),
    );
  }
}
