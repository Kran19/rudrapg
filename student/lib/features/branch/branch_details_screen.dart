import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/dummy/dummy_data.dart';
import '../rooms/room_listing_screen.dart';

class BranchDetailsScreen extends StatelessWidget {
  const BranchDetailsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final branch = DummyData.activeBranch;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Branch Details'),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Gallery Carousel / Hero Image
            SizedBox(
              height: 240,
              child: PageView.builder(
                itemCount: branch.galleryImages.length,
                itemBuilder: (context, index) {
                  return Image.network(
                    branch.galleryImages[index],
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) => Container(
                      color: AppColors.divider,
                      child: const Icon(Icons.apartment, size: 64, color: AppColors.textMuted),
                    ),
                  );
                },
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Branch Badge & Title
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.secondary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      'BRANCH CODE: ${branch.code}',
                      style: AppTypography.badge.copyWith(color: AppColors.secondary),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Text(branch.name, style: AppTypography.displayMedium),
                  const SizedBox(height: AppSpacing.xs),
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 18, color: AppColors.textSecondary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${branch.address}, ${branch.city}',
                          style: AppTypography.bodyMedium,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Manager & Emergency Contacts
                  const SectionHeader(title: 'Management Contacts'),
                  CustomCard(
                    child: Row(
                      children: [
                        CircleAvatar(
                          backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                          child: const Icon(Icons.person, color: AppColors.primary),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(branch.managerName, style: AppTypography.titleSmall),
                              Text('Branch Sub Admin Manager', style: AppTypography.bodySmall),
                            ],
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.phone_rounded, color: AppColors.success),
                          onPressed: () {},
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Amenities
                  const SectionHeader(title: 'Property Amenities'),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: branch.amenities
                        .map(
                          (amenity) => Chip(
                            avatar: const Icon(Icons.check_circle_outline, size: 16, color: AppColors.accent),
                            label: Text(amenity, style: AppTypography.bodySmall.copyWith(color: AppColors.textPrimary)),
                            backgroundColor: Colors.white,
                            side: const BorderSide(color: AppColors.divider),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                          ),
                        )
                        .toList(),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Branch Rules
                  const SectionHeader(title: 'PG Guidelines & Rules'),
                  CustomCard(
                    child: Column(
                      children: branch.rules
                          .map(
                            (rule) => Padding(
                              padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Icon(Icons.info_outline_rounded, size: 18, color: AppColors.warning),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(rule, style: AppTypography.bodyMedium),
                                  ),
                                ],
                              ),
                            ),
                          )
                          .toList(),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxxl),

                  // Primary CTA
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const RoomListingScreen()),
                        );
                      },
                      icon: const Icon(Icons.meeting_room_rounded),
                      label: const Text('View Available Rooms'),
                    ),
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
