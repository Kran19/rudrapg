import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';
import '../welcome/welcome_screen.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Settings'),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        children: [
          CustomCard(
            padding: EdgeInsets.zero,
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.privacy_tip_outlined, color: AppColors.primary),
                  title: Text('Privacy Policy', style: AppTypography.titleSmall),
                  trailing: const Icon(Icons.chevron_right_rounded),
                  onTap: () {},
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.description_outlined, color: AppColors.primary),
                  title: Text('Terms & Conditions', style: AppTypography.titleSmall),
                  trailing: const Icon(Icons.chevron_right_rounded),
                  onTap: () {},
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.info_outline_rounded, color: AppColors.primary),
                  title: Text('About Rudra Group PG App', style: AppTypography.titleSmall),
                  subtitle: Text('v1.0.0 (UI Prototype)', style: AppTypography.caption),
                  onTap: () {},
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xxl),

          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
            ),
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Reset Prototype to Onboarding'),
            onPressed: () {
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const WelcomeScreen()),
                (route) => false,
              );
            },
          ),
        ],
      ),
    );
  }
}
