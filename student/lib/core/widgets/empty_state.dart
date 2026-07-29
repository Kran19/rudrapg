import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';

class EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;
  final String? buttonText;
  final VoidCallback? onButtonPressed;

  const EmptyState({
    super.key,
    required this.icon,
    required this.title,
    required this.description,
    this.buttonText,
    this.onButtonPressed,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(AppSpacing.xxl),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.05),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 48, color: AppColors.textSecondary),
          ),
          const SizedBox(height: AppSpacing.lg),
          Text(title, style: AppTypography.titleMedium, textAlign: TextAlign.center),
          const SizedBox(height: AppSpacing.sm),
          Text(description, style: AppTypography.bodyMedium, textAlign: TextAlign.center),
          if (buttonText != null && onButtonPressed != null) ...[
            const SizedBox(height: AppSpacing.xl),
            ElevatedButton(
              onPressed: onButtonPressed,
              child: Text(buttonText!),
            ),
          ]
        ],
      ),
    );
  }
}
