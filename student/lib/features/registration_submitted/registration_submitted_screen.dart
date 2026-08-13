import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../login/login_screen.dart';

class RegistrationSubmittedScreen extends StatelessWidget {
  final String appReference;
  
  const RegistrationSubmittedScreen({super.key, required this.appReference});

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
              // Success Animated Icon Container
              Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  color: AppColors.success.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.success, width: 2),
                ),
                child: const Icon(
                  Icons.check_circle_rounded,
                  size: 64,
                  color: AppColors.success,
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Title
              Text(
                'Registration Submitted\nSuccessfully!',
                style: AppTypography.displayMedium,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppSpacing.lg),

              // Summary Card
              CustomCard(
                backgroundColor: Colors.white,
                padding: const EdgeInsets.all(AppSpacing.xl),
                child: Column(
                  children: [
                    _buildSummaryRow(
                      label: 'Application Reference:',
                      value: appReference,
                      isBold: true,
                    ),
                    const Divider(height: 24),
                    _buildSummaryRow(
                      label: 'Selected Branch:',
                      value: 'Naroda Branch (PG-NRD-01)',
                      isBold: false,
                    ),
                    const Divider(height: 24),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Approval Status:', style: AppTypography.bodySmall),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.warning.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.warning.withValues(alpha: 0.4)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.hourglass_top_rounded, size: 14, color: AppColors.warning),
                              const SizedBox(width: 4),
                              Flexible(
                                child: Text(
                                  'Pending Sub Admin Approval',
                                  style: AppTypography.caption.copyWith(
                                    color: AppColors.warning,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 24),
                    _buildSummaryRow(
                      label: 'Default Login Password:',
                      value: 'password123',
                      isBold: true,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xl),

              // Detailed Guidance Message
              CustomCard(
                backgroundColor: AppColors.primary.withValues(alpha: 0.04),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline_rounded, color: AppColors.primary, size: 24),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Text(
                        'Your registration is submitted! Step 1: Sub-Admin audits your KYC documents. Step 2: Sub-Admin assigns your Room & Bed. Log in using your registered mobile number & default password "password123".',
                        style: AppTypography.bodySmall.copyWith(
                          color: AppColors.primary,
                          height: 1.4,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const Spacer(),

              // Return to Login Button
              CustomButton(
                text: 'Return to Resident Login',
                icon: Icons.login_rounded,
                onPressed: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const LoginScreen()),
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

  Widget _buildSummaryRow({
    required String label,
    required String value,
    required bool isBold,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AppTypography.bodySmall),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: AppTypography.titleSmall.copyWith(
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: isBold ? AppColors.secondary : AppColors.textPrimary,
            ),
          ),
        ),
      ],
    );
  }
}
