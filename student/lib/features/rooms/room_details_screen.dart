import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/models/room_model.dart';
import '../bed_selection/interactive_bed_selection_screen.dart';

class RoomDetailsScreen extends StatelessWidget {
  final RoomModel room;

  const RoomDetailsScreen({super.key, required this.room});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: CustomAppBar(title: 'Room ${room.roomNumber} Details'),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Images Slider
            SizedBox(
              height: 220,
              child: PageView.builder(
                itemCount: room.images.length,
                itemBuilder: (context, index) {
                  return Image.network(
                    room.images[index],
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) => Container(
                      color: AppColors.divider,
                      child: const Icon(Icons.bed_rounded, size: 64, color: AppColors.textMuted),
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
                  // Title & Rent Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Room ${room.roomNumber}', style: AppTypography.displayMedium),
                          Text('${room.sharingType} • Floor ${room.floor}', style: AppTypography.bodyMedium),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            '₹${room.monthlyRent.toInt()}',
                            style: AppTypography.displayMedium.copyWith(color: AppColors.secondary),
                          ),
                          Text('/month rent', style: AppTypography.caption),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xl),

                  // Key Info Grid Cards
                  Row(
                    children: [
                      _buildInfoBox(
                        title: 'Security Deposit',
                        value: '₹${room.securityDeposit.toInt()}',
                        icon: Icons.shield_rounded,
                      ),
                      const SizedBox(width: AppSpacing.md),
                      _buildInfoBox(
                        title: 'Room Size',
                        value: room.roomSize,
                        icon: Icons.aspect_ratio_rounded,
                      ),
                      const SizedBox(width: AppSpacing.md),
                      _buildInfoBox(
                        title: 'Available Beds',
                        value: '${room.availableBedsCount}/${room.beds.length}',
                        icon: Icons.bed_rounded,
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Facilities & Features
                  const SectionHeader(title: 'Room Facilities'),
                  CustomCard(
                    child: Column(
                      children: [
                        _buildFeatureRow(
                          icon: Icons.ac_unit_rounded,
                          title: 'Air Conditioning',
                          subtitle: room.isAc ? 'Split AC Installed' : 'Non-AC Room',
                        ),
                        const Divider(height: AppSpacing.lg),
                        _buildFeatureRow(
                          icon: Icons.bathtub_rounded,
                          title: 'Washroom',
                          subtitle: room.hasAttachedWashroom ? 'Attached Private Bathroom' : 'Shared Bathroom',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Included Furniture
                  const SectionHeader(title: 'Included Furniture'),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: room.furniture
                        .map(
                          (item) => Chip(
                            avatar: const Icon(Icons.chair_rounded, size: 16, color: AppColors.secondary),
                            label: Text(item, style: AppTypography.bodySmall.copyWith(color: AppColors.textPrimary)),
                            backgroundColor: Colors.white,
                            side: const BorderSide(color: AppColors.divider),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          ),
                        )
                        .toList(),
                  ),
                  const SizedBox(height: AppSpacing.xxxl),

                  // Primary Bed Selection CTA
                  SizedBox(
                    width: double.infinity,
                    height: AppSpacing.buttonHeight,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.secondary,
                      ),
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => InteractiveBedSelectionScreen(room: room),
                          ),
                        );
                      },
                      icon: const Icon(Icons.grid_view_rounded),
                      label: const Text('Interactive Bed Selection'),
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

  Widget _buildInfoBox({required String title, required String value, required IconData icon}) {
    return Expanded(
      child: CustomCard(
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: 8),
        child: Column(
          children: [
            Icon(icon, color: AppColors.primary, size: 20),
            const SizedBox(height: 4),
            Text(value, style: AppTypography.titleSmall, textAlign: TextAlign.center),
            Text(title, style: AppTypography.caption, textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureRow({required IconData icon, required String title, required String subtitle}) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: AppColors.primary.withValues(alpha: 0.08),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: AppColors.primary, size: 20),
        ),
        const SizedBox(width: AppSpacing.md),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: AppTypography.titleSmall),
            Text(subtitle, style: AppTypography.bodySmall),
          ],
        ),
      ],
    );
  }
}
