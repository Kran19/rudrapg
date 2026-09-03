import 'package:dio/dio.dart';
import 'package:http_parser/http_parser.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../home/data/student_repository.dart';
import 'package:flutter/foundation.dart';

class PaymentsScreen extends ConsumerStatefulWidget {
  const PaymentsScreen({super.key});

  @override
  ConsumerState<PaymentsScreen> createState() => _PaymentsScreenState();
}

class _PaymentsScreenState extends ConsumerState<PaymentsScreen> {
  final _utrController = TextEditingController();
  XFile? _proofImage;
  bool _isSubmitting = false;

  @override
  void dispose() {
    _utrController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(StateSetter setModalState) async {
    if (!kIsWeb) {
      final status = await Permission.photos.request();
      if (!status.isGranted) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Permission required to access gallery')),
          );
        }
        return;
      }
    }
    
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
      maxWidth: 1200,
      maxHeight: 1200,
    );
    if (image != null) {
      setModalState(() {
        _proofImage = image;
      });
    }
  }

  Future<void> _submitProof() async {
    final utr = _utrController.text.trim();
    if (utr.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter UTR number.')));
      return;
    }
    if (_proofImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a payment screenshot.')));
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final formData = FormData.fromMap({
        'utr_number': utr,
        'screenshot_path': MultipartFile.fromBytes(await _proofImage!.readAsBytes(), filename: 'payment_proof.jpg', contentType: MediaType('image', 'jpeg')),
      });

      await ref.read(studentRepositoryProvider).submitPaymentProof(formData);

      if (mounted) {
        Navigator.pop(context); // close modal
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            backgroundColor: AppColors.success,
            content: Text('Payment proof submitted successfully! Branch manager will audit and verify your receipt.'),
          ),
        );
        _utrController.clear();
        setState(() {
          _proofImage = null;
        });
        ref.invalidate(paymentHistoryProvider);
        ref.invalidate(studentProfileProvider);
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
    final profileAsync = ref.watch(studentProfileProvider);
    final historyAsync = ref.watch(paymentHistoryProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Payments & Dues Ledger'),
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
              // Pending Dues Summary Header Card
              profileAsync.when(
                data: (resident) {
                  final bool isRoomSet = resident.isRoomAssigned;
                  final bool isPaid = resident.isPaid;
                  final bool isSubmitted = resident.isPaymentSubmitted;
                  final double totalDue = (isRoomSet && !isPaid)
                      ? (resident.monthlyRent + resident.securityDeposit)
                      : 0.0;

                  final String badgeText = !isRoomSet
                      ? 'AWAITING BED ALLOCATION'
                      : (isPaid
                          ? '✓ ALL DUES CLEARED'
                          : (isSubmitted
                              ? 'UNDER AUDIT ⏳'
                              : '! DUES PENDING'));

                  final Color badgeColor = !isRoomSet
                      ? Colors.white70
                      : (isPaid
                          ? AppColors.success
                          : (isSubmitted
                              ? AppColors.accent
                              : AppColors.warning));

                  final String subtitleText = !isRoomSet
                      ? 'Your room & bed have not been assigned yet. Dues will be calculated once sub-admin allocates your bed.'
                      : (isPaid
                          ? 'All rent and security deposit dues are completely cleared.'
                          : (isSubmitted
                              ? 'Your payment proof has been submitted and is currently being audited by the branch manager.'
                              : 'Initial admission payment: Rent ₹${resident.monthlyRent.toInt()} + Deposit ₹${resident.securityDeposit.toInt()}.'));

                  return Column(
                    children: [
                      CustomCard(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.all(AppSpacing.xl),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'TOTAL OUTSTANDING DUES',
                                  style: AppTypography.caption.copyWith(color: Colors.white70, letterSpacing: 1.0),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: badgeColor.withValues(alpha: 0.2),
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                  child: Text(
                                    badgeText,
                                    style: AppTypography.badge.copyWith(color: badgeColor),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: AppSpacing.sm),
                            Text(
                              isPaid ? '₹0.00' : (isRoomSet ? '₹${totalDue.toInt()}' : '₹0.00'),
                              style: AppTypography.displayLarge.copyWith(color: Colors.white),
                            ),
                            const SizedBox(height: AppSpacing.md),
                            Text(
                              subtitleText,
                              style: AppTypography.bodySmall.copyWith(color: Colors.white70),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: AppSpacing.xxl),

                      // Breakout Categories Grid
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Text('Dues Breakdown', style: AppTypography.titleLarge),
                      ),
                      const SizedBox(height: AppSpacing.md),
                      Row(
                        children: [
                          Expanded(
                            child: _buildBreakdownCard(
                              title: 'Monthly Rent',
                              amount: isRoomSet ? '₹${resident.monthlyRent.toInt()}' : 'Pending',
                              status: isPaid ? 'Paid' : (isSubmitted ? 'Verifying' : (isRoomSet ? 'Due' : 'Not Set')),
                              color: isPaid ? AppColors.success : (isSubmitted ? AppColors.accent : (isRoomSet ? AppColors.warning : Colors.grey)),
                              icon: Icons.home_rounded,
                            ),
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: _buildBreakdownCard(
                              title: 'Security Deposit',
                              amount: isRoomSet ? '₹${resident.securityDeposit.toInt()}' : 'Pending',
                              status: isPaid ? 'Paid' : (isSubmitted ? 'Verifying' : (isRoomSet ? 'Due' : 'Not Set')),
                              color: isPaid ? AppColors.success : (isSubmitted ? AppColors.accent : (isRoomSet ? AppColors.warning : Colors.grey)),
                              icon: Icons.security_rounded,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.md),
                      Row(
                        children: [
                          Expanded(
                            child: _buildBreakdownCard(
                              title: 'Electricity Meter',
                              amount: 'As Consumed',
                              status: 'Meter Audit',
                              color: AppColors.secondary,
                              icon: Icons.bolt_rounded,
                            ),
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: _buildBreakdownCard(
                              title: 'Payable Today',
                              amount: isPaid ? '₹0.00' : (isRoomSet ? '₹${totalDue.toInt()}' : '₹0.00'),
                              status: isPaid ? 'Clear' : (isSubmitted ? 'Auditing' : (isRoomSet ? 'Payable' : 'Pending')),
                              color: isPaid ? AppColors.success : (isSubmitted ? AppColors.accent : (isRoomSet ? AppColors.warning : Colors.grey)),
                              icon: Icons.check_circle_rounded,
                            ),
                          ),
                        ],
                      ),
                    ],
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => const SizedBox(),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Branch UPI & Payment Proof Upload Card
              Text('Offline / P2P Payment Proof', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: AppColors.secondary.withValues(alpha: 0.12),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.qr_code_rounded, color: AppColors.secondary, size: 24),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Branch UPI Handle', style: AppTypography.titleSmall),
                              const SizedBox(height: 2),
                              Text('rudra.naroda@upi • GPay / PhonePe', style: AppTypography.bodySmall),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    CustomButton(
                      text: 'Upload Payment Proof Screenshot',
                      icon: Icons.cloud_upload_outlined,
                      outlined: true,
                      onPressed: () => _showUploadProofModal(context),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Payment Timeline & History
              Text('Payment History', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              historyAsync.when(
                data: (history) {
                  if (history.isEmpty) {
                    return const Text('No payment history found.');
                  }
                  return ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: history.length,
                    separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, index) {
                      final item = history[index];
                      final amount = (double.tryParse(item['amount']?.toString() ?? '0') ?? 0).toInt();
                      return CustomCard(
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.success.withValues(alpha: 0.12),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.receipt_long_rounded, color: AppColors.success, size: 20),
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(item['payment_type'] ?? 'PAYMENT', style: AppTypography.titleSmall),
                                  const SizedBox(height: 2),
                                  Text('${item['payment_mode']} • ${item['tx_reference']}', style: AppTypography.bodySmall),
                                  Text(item['payment_date'] ?? '', style: AppTypography.caption),
                                ],
                              ),
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  '₹$amount',
                                  style: AppTypography.titleSmall.copyWith(color: AppColors.success),
                                ),
                                const SizedBox(height: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppColors.success.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    item['status'] ?? 'PENDING',
                                    style: AppTypography.caption.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => Text('Error loading payments: $err'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildBreakdownCard({
    required String title,
    required String amount,
    required String status,
    required Color color,
    required IconData icon,
  }) {
    return CustomCard(
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Icon(icon, color: color, size: 20),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  status,
                  style: AppTypography.caption.copyWith(color: color, fontWeight: FontWeight.bold, fontSize: 10),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Text(title, style: AppTypography.caption.copyWith(color: AppColors.textSecondary)),
          const SizedBox(height: 2),
          Text(amount, style: AppTypography.titleMedium.copyWith(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  void _showUploadProofModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final isProofSelected = _proofImage != null;
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
                top: 24,
                left: 24,
                right: 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Upload Payment Proof', style: AppTypography.titleLarge),
                  const SizedBox(height: 4),
                  Text(
                    'Attach screenshot of your GPay/PhonePe transfer & enter UTR number.',
                    style: AppTypography.bodySmall,
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  CustomTextField(
                    label: 'UPI Transaction Ref / UTR Number',
                    hint: '12-digit UTR reference code',
                    prefixIcon: Icons.tag_rounded,
                    controller: _utrController,
                  ),
                  const SizedBox(height: AppSpacing.md),

                  CustomCard(
                    onTap: () {
                      _pickImage(setModalState);
                    },
                    backgroundColor: isProofSelected ? AppColors.success.withValues(alpha: 0.05) : Colors.white,
                    border: Border.all(color: isProofSelected ? AppColors.success : AppColors.divider),
                    child: Row(
                      children: [
                        Icon(
                          isProofSelected ? Icons.check_circle_rounded : Icons.add_photo_alternate_rounded,
                          color: isProofSelected ? AppColors.success : AppColors.secondary,
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: Text(
                            isProofSelected ? 'File Attached: ${_proofImage!.name}' : 'Select Screenshot Image File',
                            style: AppTypography.bodyMedium,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  CustomButton(
                    text: 'Submit Proof to Manager',
                    icon: Icons.send_rounded,
                    isLoading: _isSubmitting,
                    onPressed: _submitProof,
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
