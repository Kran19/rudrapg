import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/status_badge.dart';
import '../../data/models/bed_model.dart';
import '../../data/models/room_model.dart';
import '../main_layout/main_layout_screen.dart';

class BookingSubmittedScreen extends StatelessWidget {
  final RoomModel room;
  final BedModel selectedBed;

  const BookingSubmittedScreen({
    super.key,
    required this.room,
    required this.selectedBed,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            children: [
              const Spacer(),
              // Success Illustration Graphic
              Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  color: AppColors.success.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: const Center(
                  child: Icon(
                    Icons.check_circle_rounded,
                    size: 64,
                    color: AppColors.success,
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),
              Text(
                'Booking Submitted!',
                style: AppTypography.displayLarge,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppSpacing.sm),
              Text(
                'Your application has been received. The Sub Admin manager at Naroda Branch will verify your details and approve your bed key allocation.',
                style: AppTypography.bodyLarge,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Booking Application Details Card
              CustomCard(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Booking Ref ID', style: AppTypography.bodySmall),
                        Text('BK-2026-8812', style: AppTypography.titleSmall.copyWith(color: AppColors.secondary)),
                      ],
                    ),
                    const Divider(height: AppSpacing.lg),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Branch Name', style: AppTypography.bodySmall),
                        Text('Naroda Branch', style: AppTypography.titleSmall),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Allocated Bed', style: AppTypography.bodySmall),
                        Text('Room ${room.roomNumber} (${selectedBed.code})', style: AppTypography.titleSmall),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Monthly Rent', style: AppTypography.bodySmall),
                        Text('₹${room.monthlyRent.toInt()}/month', style: AppTypography.titleSmall),
                      ],
                    ),
                    const Divider(height: AppSpacing.lg),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Status', style: AppTypography.bodySmall),
                        StatusBadge.pending(),
                      ],
                    ),
                  ],
                ),
              ),
              const Spacer(),

              // Return to Home CTA
              CustomButton(
                text: 'Go to Resident Dashboard',
                icon: Icons.home_rounded,
                onPressed: () {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (context) => const MainLayoutScreen()),
                    (route) => false,
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
