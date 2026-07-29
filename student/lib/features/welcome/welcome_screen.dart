import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../main_layout/main_layout_screen.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Spacer(),
              // Hero Illustration Container
              Center(
                child: Container(
                  height: 240,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.05),
                    borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
                    border: Border.all(color: AppColors.divider),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: AppColors.secondary.withValues(alpha: 0.12),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.qr_code_scanner_rounded,
                          size: 64,
                          color: AppColors.secondary,
                        ),
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppColors.success.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '✓ Branch Auto-Detected via QR',
                          style: AppTypography.caption.copyWith(
                            color: AppColors.success,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const Spacer(),

              // Text Content
              Text(
                'Welcome to\nNaroda Branch',
                style: AppTypography.displayLarge,
              ),
              const SizedBox(height: AppSpacing.md),
              Text(
                'You are visiting Rudra Boys PG. Select your room & bed, upload registration details, pay monthly rent, and submit electricity readings directly from your mobile phone.',
                style: AppTypography.bodyLarge.copyWith(
                  color: AppColors.textSecondary,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: AppSpacing.xxxl),

              // Action Button
              CustomButton(
                text: 'Explore Branch & Rooms',
                icon: Icons.arrow_forward_rounded,
                onPressed: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const MainLayoutScreen()),
                  );
                },
              ),
              const SizedBox(height: AppSpacing.md),
            ],
          ),
        ),
      ),
    );
  }
}
