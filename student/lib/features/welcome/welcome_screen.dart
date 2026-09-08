import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../login/login_screen.dart';
import '../student_registration/student_registration_screen.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final padding = AppSpacing.responsivePagePadding(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: constraints.maxHeight,
                ),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: AppSpacing.maxContentWidth),
                    child: Padding(
                      padding: padding,
                      child: IntrinsicHeight(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: AppSpacing.lg),
                            // QR Code Auto-Detected Hero Illustration with Official Rudra PG Logo
                            Center(
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: AppSpacing.xxl, horizontal: AppSpacing.lg),
                                width: double.infinity,
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withValues(alpha: 0.04),
                                  borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
                                  border: Border.all(color: AppColors.divider),
                                ),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(20),
                                      child: Image.asset(
                                        'assets/icons/logo.png',
                                        width: 76,
                                        height: 76,
                                        fit: BoxFit.cover,
                                        errorBuilder: (context, error, stackTrace) => Container(
                                          padding: const EdgeInsets.all(18),
                                          decoration: BoxDecoration(
                                            color: AppColors.secondary.withValues(alpha: 0.12),
                                            shape: BoxShape.circle,
                                          ),
                                          child: const Icon(
                                            Icons.apartment_rounded,
                                            size: 44,
                                            color: AppColors.secondary,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: AppSpacing.md),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: AppColors.success.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(color: AppColors.success.withValues(alpha: 0.3)),
                                      ),
                                      child: Text(
                                        '✓ Branch QR Code Auto-Detected',
                                        style: AppTypography.caption.copyWith(
                                          color: AppColors.success,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: AppSpacing.xl),

                            // Welcome Heading
                            Text(
                              'Welcome to\nNaroda Branch',
                              style: AppTypography.displayLarge,
                            ),
                            const SizedBox(height: AppSpacing.sm),
                            Text(
                              'You have scanned the physical QR code at Rudra Group PG (Naroda Branch). Submit your resident registration details below for manager verification & bed allocation.',
                              style: AppTypography.bodyLarge.copyWith(
                                color: AppColors.textSecondary,
                                height: 1.5,
                              ),
                            ),
                            const Spacer(),
                            const SizedBox(height: AppSpacing.xl),

                            // Primary Registration Button
                            CustomButton(
                              text: 'Register For Naroda Branch',
                              icon: Icons.assignment_outlined,
                              onPressed: () {
                                Navigator.of(context).push(
                                  MaterialPageRoute(builder: (context) => const StudentRegistrationScreen()),
                                );
                              },
                            ),
                            const SizedBox(height: AppSpacing.md),

                            // Secondary Login Button
                            Center(
                              child: TextButton.icon(
                                onPressed: () {
                                  Navigator.of(context).pushReplacement(
                                    MaterialPageRoute(builder: (context) => const LoginScreen()),
                                  );
                                },
                                icon: const Icon(Icons.login_rounded, size: 18, color: AppColors.secondary),
                                label: Text(
                                  'Already an Approved Resident? Log In',
                                  style: AppTypography.titleSmall.copyWith(color: AppColors.secondary),
                                ),
                              ),
                            ),
                            const SizedBox(height: AppSpacing.sm),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
