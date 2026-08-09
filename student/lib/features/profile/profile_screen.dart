import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../electricity/electricity_screen.dart';
import '../login/login_screen.dart';
import '../resident/my_room_screen.dart';
import '../settings/settings_screen.dart';
import '../support/support_screen.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../home/data/student_repository.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(studentProfileProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Resident Profile'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: profileAsync.when(
          data: (resident) => SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Profile Header Card
                CustomCard(
                  padding: const EdgeInsets.all(AppSpacing.xl),
                  child: Row(
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                          boxShadow: AppSpacing.softShadow,
                        ),
                        child: Center(
                          child: Text(
                            resident.fullName.isNotEmpty ? resident.fullName[0].toUpperCase() : 'S',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 22),
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.lg),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(resident.fullName.isNotEmpty ? resident.fullName : 'Resident', style: AppTypography.titleLarge),
                            const SizedBox(height: 2),
                            Text(
                              resident.branchName,
                              style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppColors.success.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                '✓ ${resident.kycStatus.toUpperCase()}',
                                style: AppTypography.caption.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),

              // Personal Information Section
              Text('Personal Information', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildInfoTile(icon: Icons.phone_android_rounded, label: 'Mobile Number', value: resident.phone),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.email_outlined, label: 'Email Address', value: resident.email),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.badge_outlined, label: 'Aadhaar Number', value: resident.aadhaarNumber),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.credit_card_rounded, label: 'PAN Card Number', value: resident.panNumber),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Parent & Emergency Details
              Text('Parent & Emergency Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildInfoTile(icon: Icons.family_restroom_rounded, label: 'Parent / Guardian', value: resident.parentName),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.phone_callback_rounded, label: 'Parent Phone', value: resident.parentPhone),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.contact_phone_outlined, label: 'Emergency Contact', value: resident.emergencyContact),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.location_on_outlined, label: 'Permanent Address', value: resident.currentAddress),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Navigation Quick Links
              Text('Resident Management', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildNavTile(
                      icon: Icons.bed_rounded,
                      title: 'My Room Details',
                      subtitle: 'View assigned floor, bed & manager contact',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const MyRoomScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.electric_meter_rounded,
                      title: 'Electricity Meter Readings',
                      subtitle: 'View consumption & submit meter photo',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const ElectricityScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.headset_mic_rounded,
                      title: 'Support & Complaints Desk',
                      subtitle: 'Call manager, WhatsApp support & raise tickets',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const SupportScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.settings_outlined,
                      title: 'App Settings & Policy',
                      subtitle: 'Privacy policy, terms & help desk',
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
              CustomCard(
                onTap: () => _confirmSignOut(context),
                backgroundColor: AppColors.error.withValues(alpha: 0.05),
                border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.logout_rounded, color: AppColors.error, size: 20),
                    const SizedBox(width: AppSpacing.sm),
                    Text(
                      'Sign Out of Resident Account',
                      style: AppTypography.titleSmall.copyWith(color: AppColors.error, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error loading profile: $err')),
      ),
    ),
    );
  }

  Widget _buildInfoTile({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.secondary),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: AppTypography.caption.copyWith(color: AppColors.textSecondary)),
              const SizedBox(height: 2),
              Text(value, style: AppTypography.bodyMedium.copyWith(fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildNavTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: AppColors.primary, size: 20),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: AppTypography.titleSmall),
                  const SizedBox(height: 2),
                  Text(subtitle, style: AppTypography.caption),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }

  void _confirmSignOut(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign Out'),
        content: const Text('Are you sure you want to sign out of your resident companion portal?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
            onPressed: () {
              Navigator.pop(context);
              Navigator.of(context).pushReplacement(
                MaterialPageRoute(builder: (context) => const LoginScreen()),
              );
            },
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }
}
