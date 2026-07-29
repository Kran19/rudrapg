import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';

class CustomCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;
  final Color? backgroundColor;
  final Border? border;
  final List<BoxShadow>? shadow;

  const CustomCard({
    super.key,
    required this.child,
    this.padding,
    this.onTap,
    this.backgroundColor,
    this.border,
    this.shadow,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: backgroundColor ?? AppColors.card,
        borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
        border: border ?? Border.all(color: AppColors.divider, width: 1),
        boxShadow: shadow ?? AppSpacing.cardShadow,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
          onTap: onTap,
          child: Padding(
            padding: padding ?? const EdgeInsets.all(AppSpacing.lg),
            child: child,
          ),
        ),
      ),
    );
  }
}
