import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/dummy/dummy_data.dart';

class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final branch = DummyData.activeBranch;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Support & Complaints'),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Direct Contact Actions
            Row(
              children: [
                Expanded(
                  child: CustomCard(
                    onTap: () {},
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: const BoxDecoration(
                            color: AppColors.success,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.phone_rounded, color: Colors.white, size: 24),
                        ),
                        const SizedBox(height: AppSpacing.sm),
                        Text('Call Manager', style: AppTypography.titleSmall),
                        Text(branch.managerPhone, style: AppTypography.caption),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: CustomCard(
                    onTap: () {},
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: const BoxDecoration(
                            color: Color(0xFF25D366), // WhatsApp Green
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.chat_bubble_rounded, color: Colors.white, size: 24),
                        ),
                        const SizedBox(height: AppSpacing.sm),
                        Text('WhatsApp Support', style: AppTypography.titleSmall),
                        Text('Instant Chat', style: AppTypography.caption),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Raise Complaint Banner
            CustomCard(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('NEED MAINTENANCE ASSISTANCE?', style: AppTypography.badge.copyWith(color: AppColors.accent)),
                  const SizedBox(height: 4),
                  Text('Raise a Support Ticket', style: AppTypography.titleLarge.copyWith(color: Colors.white)),
                  const SizedBox(height: 4),
                  Text('Plumbing, Electrical, Wi-Fi, or Housekeeping issues', style: AppTypography.bodySmall.copyWith(color: Colors.white70)),
                  const SizedBox(height: AppSpacing.lg),
                  CustomButton(
                    text: 'Raise Complaint Ticket',
                    type: CustomButtonType.secondary,
                    height: 44,
                    onPressed: () => _showRaiseComplaintDialog(context),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // FAQs Section
            SectionHeader(title: 'Frequently Asked Questions'),
            _buildFaqTile('When is the monthly rent due?', 'Rent is due on or before the 5th of every month.'),
            _buildFaqTile('How is electricity calculated?', 'Readings are multiplied by the branch rate of ₹10/unit and divided among room occupants.'),
            _buildFaqTile('What is the procedure for vacating?', '1-month advance notice is required to claim full security deposit return.'),
          ],
        ),
      ),
    );
  }

  Widget _buildFaqTile(String question, String answer) {
    return ExpansionTile(
      title: Text(question, style: AppTypography.titleSmall),
      childrenPadding: const EdgeInsets.all(AppSpacing.md),
      children: [
        Text(answer, style: AppTypography.bodyMedium),
      ],
    );
  }

  void _showRaiseComplaintDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppSpacing.radiusCard)),
          title: Text('Raise Complaint Ticket', style: AppTypography.titleLarge),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                initialValue: 'Plumbing',
                decoration: const InputDecoration(labelText: 'Category'),
                items: const [
                  DropdownMenuItem(value: 'Plumbing', child: Text('Plumbing / Bathroom')),
                  DropdownMenuItem(value: 'Electrical', child: Text('Electrical / AC')),
                  DropdownMenuItem(value: 'WiFi', child: Text('Wi-Fi / Internet')),
                  DropdownMenuItem(value: 'Cleaning', child: Text('Housekeeping')),
                ],
                onChanged: (_) {},
              ),
              const SizedBox(height: AppSpacing.md),
              TextFormField(
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Complaint Description',
                  hintText: 'Describe the issue...',
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Complaint ticket submitted! Ticket ID: TCK-2026-09')),
                );
              },
              child: const Text('Submit Ticket'),
            ),
          ],
        );
      },
    );
  }
}
