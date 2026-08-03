import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../data/dummy/dummy_data.dart';

class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final branch = DummyData.activeBranch;
    final faqs = DummyData.faqs;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Resident Support & Help Desk'),
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
              // Contact Branch Manager Actions
              Text('Direct Manager Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  Expanded(
                    child: CustomCard(
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Calling branch manager: ${branch.managerPhone}')),
                        );
                      },
                      backgroundColor: AppColors.success.withValues(alpha: 0.08),
                      border: Border.all(color: AppColors.success.withValues(alpha: 0.3)),
                      child: Column(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.success.withValues(alpha: 0.2),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.phone_rounded, color: AppColors.success, size: 24),
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Text('Call Manager', style: AppTypography.titleSmall),
                          const SizedBox(height: 2),
                          Text(branch.managerPhone, style: AppTypography.caption),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: CustomCard(
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Opening WhatsApp Support chat...')),
                        );
                      },
                      backgroundColor: AppColors.secondary.withValues(alpha: 0.08),
                      border: Border.all(color: AppColors.secondary.withValues(alpha: 0.3)),
                      child: Column(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.secondary.withValues(alpha: 0.2),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.chat_rounded, color: AppColors.secondary, size: 24),
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Text('WhatsApp Support', style: AppTypography.titleSmall),
                          const SizedBox(height: 2),
                          Text('Quick Chat', style: AppTypography.caption),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Raise Maintenance Complaint Ticket
              Text('Raise Maintenance Request', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Select Category', style: AppTypography.caption.copyWith(color: AppColors.textSecondary)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: 'Plumbing',
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Plumbing', child: Text('Plumbing & Geyser')),
                        DropdownMenuItem(value: 'Electrical', child: Text('Electrical & Lights')),
                        DropdownMenuItem(value: 'Wi-Fi', child: Text('Wi-Fi & Internet')),
                        DropdownMenuItem(value: 'Cleaning', child: Text('Room Housekeeping')),
                      ],
                      onChanged: (val) {},
                    ),
                    const SizedBox(height: AppSpacing.md),
                    const CustomTextField(
                      label: 'Complaint Description',
                      hint: 'Describe issue in detail...',
                      maxLines: 3,
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    CustomButton(
                      text: 'Submit Ticket to Manager',
                      icon: Icons.assignment_turned_in_rounded,
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Support ticket raised! Manager notified.')),
                        );
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Frequently Asked Questions
              Text('Resident FAQs', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: faqs.length,
                separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
                itemBuilder: (context, index) {
                  final faq = faqs[index];
                  return CustomCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.help_outline_rounded, size: 20, color: AppColors.secondary),
                            const SizedBox(width: 8),
                            Expanded(child: Text(faq['question']!, style: AppTypography.titleSmall)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(faq['answer']!, style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary, height: 1.5)),
                      ],
                    ),
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
