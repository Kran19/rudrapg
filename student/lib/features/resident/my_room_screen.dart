import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/dummy/dummy_data.dart';

class MyRoomScreen extends StatelessWidget {
  const MyRoomScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final resident = DummyData.sampleResident;
    final branch = DummyData.activeBranch;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'My Room & Stay'),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Room Header Summary Banner Card
            CustomCard(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.accent.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          resident.sharingType.toUpperCase(),
                          style: AppTypography.badge.copyWith(color: AppColors.accent),
                        ),
                      ),
                      Text(
                        'Joined ${resident.joiningDate}',
                        style: AppTypography.caption.copyWith(color: Colors.white70),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    'Room ${resident.roomNumber}',
                    style: AppTypography.displayLarge.copyWith(color: Colors.white),
                  ),
                  Text(
                    'Bed Identifier: ${resident.bedCode}',
                    style: AppTypography.titleMedium.copyWith(color: AppColors.accent),
                  ),
                  const Divider(color: Colors.white24, height: AppSpacing.xl),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Monthly Tariff', style: AppTypography.caption.copyWith(color: Colors.white60)),
                          Text('₹${resident.monthlyRent.toInt()}', style: AppTypography.titleMedium.copyWith(color: Colors.white)),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('Security Deposit Held', style: AppTypography.caption.copyWith(color: Colors.white60)),
                          Text('₹${resident.securityDeposit.toInt()}', style: AppTypography.titleMedium.copyWith(color: Colors.white)),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Roommates Information Section
            const SectionHeader(title: 'Roommate Details'),
            CustomCard(
              child: Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.secondary.withValues(alpha: 0.12),
                    child: const Icon(Icons.person, color: AppColors.secondary),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Amit Verma', style: AppTypography.titleSmall),
                        Text('Bed 1A • Student (GTU)', style: AppTypography.bodySmall),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.success.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text('Active', style: AppTypography.caption.copyWith(color: AppColors.success)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Branch Manager Support Card
            const SectionHeader(title: 'Branch Management'),
            CustomCard(
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.headset_mic_rounded, color: AppColors.primary),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(branch.managerName, style: AppTypography.titleSmall),
                        Text('Branch Manager • ${branch.contactNumber}', style: AppTypography.bodySmall),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.phone_in_talk_rounded, color: AppColors.success),
                    onPressed: () {},
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
