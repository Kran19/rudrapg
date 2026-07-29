import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_typography.dart';

class CustomAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final bool showBackButton;
  final List<Widget>? actions;

  const CustomAppBar({
    super.key,
    required this.title,
    this.showBackButton = true,
    this.actions,
  });

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: AppColors.background,
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: false,
      leading: showBackButton
          ? IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 20, color: AppColors.primary),
              onPressed: () => Navigator.of(context).pop(),
            )
          : null,
      title: Text(
        title,
        style: AppTypography.titleLarge,
      ),
      actions: actions,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}
