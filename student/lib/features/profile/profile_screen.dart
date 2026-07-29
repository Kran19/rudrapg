import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';
import '../../data/dummy/dummy_data.dart';
import '../resident/my_room_screen.dart';
import '../resident/documents_screen.dart';
import '../support/support_screen.dart';
import '../settings/settings_screen.dart';
import '../welcome/welcome_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final resident = DummyData.sampleResident;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'My Profile', showBackButton: false),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          children: [
            // Avatar Header Card
            CustomCard(
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: AppColors.primary,
                    child: Text(
                      'RS',
                      style: AppTypography.displayLarge.copyWith(color: Colors.white),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(resident.fullName, style: AppTypography.titleLarge),
                  const SizedBox(height: 2),
                  Text(resident.phone, style: AppTypography.bodyMedium),
                  const SizedBox(height: AppSpacing.sm),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.secondary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '${resident.branchName} • Room ${resident.roomNumber}',
                      style: AppTypography.caption.copyWith(color: AppColors.secondary, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Profile Options Menu
            CustomCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  _buildProfileTile(
                    icon: Icons.bed_rounded,
                    title: 'My Room & Stay',
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const MyRoomScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  _buildProfileTile(
                    icon: Icons.folder_shared_outlined,
                    title: 'My Documents & Receipts',
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const DocumentsScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  _buildProfileTile(
                    icon: Icons.contact_phone_outlined,
                    title: 'Emergency Contacts',
                    subtitle: '${resident.emergencyContactName} • ${resident.emergencyContactPhone}',
                    onTap: () {},
                  ),
                  const Divider(height: 1),
                  _buildProfileTile(
                    icon: Icons.help_outline_rounded,
                    title: 'Support & Help Desk',
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const SupportScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  _buildProfileTile(
                    icon: Icons.settings_outlined,
                    title: 'App Settings',
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const SettingsScreen()),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Logout Button
            TextButton.icon(
              style: TextButton.styleFrom(
                foregroundColor: AppColors.error,
              ),
              icon: const Icon(Icons.logout_rounded),
              label: const Text('Exit & Reset Prototype Session'),
              onPressed: () {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (context) => const WelcomeScreen()),
                  (route) => false,
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileTile({
    required IconData icon,
    required String title,
    String? subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.primary.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: AppColors.primary, size: 20),
      ),
      title: Text(title, style: AppTypography.titleSmall),
      subtitle: subtitle != null ? Text(subtitle, style: AppTypography.caption) : null,
      trailing: const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
      onTap: onTap,
    );
  }
}
