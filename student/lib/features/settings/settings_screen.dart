import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../home/data/student_repository.dart';
import '../login/login_screen.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('App Settings & Policies'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Legal & App Information', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildSettingsTile(
                      icon: Icons.shield_outlined,
                      title: 'Privacy Policy',
                      subtitle: 'How we protect your personal resident data',
                      onTap: () {
                        _showPolicyDialog(
                          context,
                          'Privacy Policy',
                          'Rudra Group PG Privacy Policy\n\n1. Information We Collect:\nWe collect personal identification details (Full Name, Phone Number, Email, Aadhaar, PAN) and KYC document images for resident verification, security, and hostel stay compliance.\n\n2. How We Use Data:\nResident data is strictly used for stay management, room allocation, digital rent ledgers, electricity billing, and emergency guardian contact.\n\n3. Data Storage & Security:\nData is transmitted using encrypted HTTPS channels and stored in secure PostgreSQL databases with role-based access control.\n\n4. Your Rights:\nResidents can review their profile data and request account deletion via this app or by contacting branch management.',
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildSettingsTile(
                      icon: Icons.description_outlined,
                      title: 'Terms & Conditions of Stay',
                      subtitle: 'PG stay rules, notice period & deposit policy',
                      onTap: () {
                        _showPolicyDialog(
                          context,
                          'Terms & Conditions of Stay',
                          'Rudra Group PG Terms of Stay\n\n1. Rent & Dues:\nMonthly rent is due on or before the 5th of every month. Digital payment receipts must be submitted via UPI or bank transfer.\n\n2. Security Deposit:\nSecurity deposits are refundable upon checkout, subject to room inspection, clearing of electricity dues, and completion of notice period.\n\n3. Notice Period:\nA mandatory 30-day notice must be submitted before vacating the PG room.\n\n4. Code of Conduct:\nResidents must adhere to branch curfew, noise regulations, and cleanliness policies.',
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildSettingsTile(
                      icon: Icons.info_outline_rounded,
                      title: 'About Rudra Group PG',
                      subtitle: 'App version 2.4.0 (Enterprise SaaS Edition)',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Rudra Group PG Resident Companion v2.4.0')),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildSettingsTile(
                      icon: Icons.help_outline_rounded,
                      title: 'Help Desk & Troubleshooting',
                      subtitle: 'App usage guide & resident support',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Opening Help Desk...')),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildSettingsTile(
                      icon: Icons.delete_forever_rounded,
                      title: 'Delete Resident Account',
                      subtitle: 'Request account removal & data deletion',
                      onTap: () => _confirmAccountDeletion(context, ref),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Logout Action Button
              CustomCard(
                onTap: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const LoginScreen()),
                  );
                },
                backgroundColor: AppColors.error.withValues(alpha: 0.05),
                border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.logout_rounded, color: AppColors.error, size: 20),
                    const SizedBox(width: AppSpacing.sm),
                    Flexible(
                      child: Text(
                        'Sign Out',
                        style: AppTypography.titleSmall.copyWith(color: AppColors.error, fontWeight: FontWeight.bold),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showPolicyDialog(BuildContext context, String title, String content) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title, style: AppTypography.titleLarge),
        content: SingleChildScrollView(
          child: Text(content, style: AppTypography.bodyMedium),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  void _confirmAccountDeletion(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppColors.error),
            SizedBox(width: 8),
            Expanded(
              child: Text('Delete Account?'),
            ),
          ],
        ),
        content: const Text(
          'Requesting account deletion will initiate resident data removal and revoke app access. Active stay contracts, pending rent dues, and deposit settlements will be audited by branch administration in accordance with PG policy.\n\nAre you sure you want to proceed?',
          style: TextStyle(fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogCtx).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            onPressed: () async {
              Navigator.of(dialogCtx).pop();
              try {
                await ref.read(studentRepositoryProvider).deleteAccount();
                final prefs = await SharedPreferences.getInstance();
                await prefs.remove('auth_token');
                await prefs.remove('user_role');
              } catch (_) {
                // Proceed with local logout even if network fails
              }
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Account deletion request submitted. Logging out...'),
                    backgroundColor: AppColors.error,
                  ),
                );
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                );
              }
            },
            child: const Text('Delete Account', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingsTile({
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
                color: AppColors.secondary.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: AppColors.secondary, size: 20),
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
}
