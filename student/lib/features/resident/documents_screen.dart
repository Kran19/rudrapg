import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_card.dart';

class DocumentsScreen extends StatelessWidget {
  const DocumentsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'My Documents'),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        children: [
          _buildDocTile(
            title: 'Aadhaar Card (Front & Back)',
            subtitle: 'Uploaded on 01 Aug 2026 • Verified',
            icon: Icons.badge_outlined,
            isVerified: true,
          ),
          const SizedBox(height: AppSpacing.md),
          _buildDocTile(
            title: 'PAN Card Copy',
            subtitle: 'Uploaded on 01 Aug 2026 • Verified',
            icon: Icons.credit_card_rounded,
            isVerified: true,
          ),
          const SizedBox(height: AppSpacing.md),
          _buildDocTile(
            title: 'Security Deposit Receipt',
            subtitle: 'Issued by Branch Manager',
            icon: Icons.receipt_long_rounded,
            isVerified: true,
          ),
          const SizedBox(height: AppSpacing.md),
          _buildDocTile(
            title: 'July Rent Payment Receipt',
            subtitle: 'Transaction Ref: PAY-2026-9912',
            icon: Icons.description_outlined,
            isVerified: true,
          ),
        ],
      ),
    );
  }

  Widget _buildDocTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required bool isVerified,
  }) {
    return CustomCard(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppColors.primary, size: 24),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: AppTypography.titleSmall),
                const SizedBox(height: 2),
                Text(subtitle, style: AppTypography.bodySmall),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.remove_red_eye_outlined, color: AppColors.secondary),
            onPressed: () {},
          ),
        ],
      ),
    );
  }
}
