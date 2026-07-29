import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';

class CustomTextField extends StatelessWidget {
  final String label;
  final String hint;
  final TextEditingController? controller;
  final TextInputType keyboardType;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final bool readOnly;
  final VoidCallback? onTap;
  final int maxLines;
  final String? initialValue;

  const CustomTextField({
    super.key,
    required this.label,
    required this.hint,
    this.controller,
    this.keyboardType = TextInputType.text,
    this.prefixIcon,
    this.suffixIcon,
    this.readOnly = false,
    this.onTap,
    this.maxLines = 1,
    this.initialValue,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: AppTypography.titleSmall.copyWith(
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: AppSpacing.sm),
        TextFormField(
          controller: controller,
          initialValue: initialValue,
          keyboardType: keyboardType,
          readOnly: readOnly,
          onTap: onTap,
          maxLines: maxLines,
          style: AppTypography.bodyLarge,
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: prefixIcon != null ? Icon(prefixIcon, color: AppColors.textSecondary, size: 20) : null,
            suffixIcon: suffixIcon,
          ),
        ),
      ],
    );
  }
}
