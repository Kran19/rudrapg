import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../data/dummy/dummy_data.dart';
import '../home/data/student_repository.dart';

class SupportScreen extends ConsumerStatefulWidget {
  const SupportScreen({super.key});

  @override
  ConsumerState<SupportScreen> createState() => _SupportScreenState();
}

class _SupportScreenState extends ConsumerState<SupportScreen> {
  final _descController = TextEditingController();
  final _subjectController = TextEditingController();
  String _category = 'Plumbing';
  bool _isSubmitting = false;

  @override
  void dispose() {
    _descController.dispose();
    _subjectController.dispose();
    super.dispose();
  }

  Future<void> _submitComplaint() async {
    final desc = _descController.text.trim();
    final sub = _subjectController.text.trim();
    if (desc.isEmpty || sub.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter subject and description.')));
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      await ref.read(studentRepositoryProvider).createComplaint({
        'category': _category,
        'subject': sub,
        'description': desc,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Support ticket raised!')));
        _descController.clear();
        _subjectController.clear();
        ref.invalidate(complaintHistoryProvider);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final faqs = DummyData.faqs;
    final historyAsync = ref.watch(complaintHistoryProvider);
    final profileAsync = ref.watch(studentProfileProvider);

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
                        if (profileAsync.hasValue) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Calling branch manager: ${profileAsync.value!.branchName}')),
                          );
                        }
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
                          Text('Dial Support', style: AppTypography.caption),
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
                      onChanged: (val) {
                        setState(() {
                          _category = val ?? 'Plumbing';
                        });
                      },
                    ),
                    const SizedBox(height: AppSpacing.md),
                    CustomTextField(
                      label: 'Subject',
                      hint: 'Short summary of issue',
                      controller: _subjectController,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    CustomTextField(
                      label: 'Complaint Description',
                      hint: 'Describe issue in detail...',
                      maxLines: 3,
                      controller: _descController,
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    CustomButton(
                      text: 'Submit Ticket to Manager',
                      icon: Icons.assignment_turned_in_rounded,
                      isLoading: _isSubmitting,
                      onPressed: _submitComplaint,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Complaint History
              Text('My Tickets', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              historyAsync.when(
                data: (history) {
                  if (history.isEmpty) {
                    return const Text('No tickets raised.');
                  }
                  return ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: history.length,
                    separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, index) {
                      final item = history[index];
                      return CustomCard(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text('${item['ticket_number']} • ${item['category']}', style: AppTypography.titleSmall),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppColors.accent.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    item['status'] ?? 'PENDING',
                                    style: AppTypography.caption.copyWith(color: AppColors.accent, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(item['subject'] ?? '', style: AppTypography.bodySmall.copyWith(fontWeight: FontWeight.bold)),
                            const SizedBox(height: 4),
                            Text(item['description'] ?? '', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                          ],
                        ),
                      );
                    },
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => Text('Error loading tickets: $err'),
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
