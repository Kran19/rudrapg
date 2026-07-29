import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/payment_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/dummy/dummy_data.dart';

class PaymentsScreen extends StatefulWidget {
  const PaymentsScreen({super.key});

  @override
  State<PaymentsScreen> createState() => _PaymentsScreenState();
}

class _PaymentsScreenState extends State<PaymentsScreen> {
  @override
  Widget build(BuildContext context) {
    final payments = DummyData.paymentHistory;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'My Payments & Dues', showBackButton: false),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Dues Overview Banner
            CustomCard(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('TOTAL OUTSTANDING DUE', style: AppTypography.badge.copyWith(color: AppColors.warning)),
                  const SizedBox(height: 4),
                  Text('₹6,500.00', style: AppTypography.displayLarge.copyWith(color: Colors.white)),
                  const SizedBox(height: AppSpacing.sm),
                  Text('August Monthly Rent • Due by 5th Aug', style: AppTypography.bodySmall.copyWith(color: Colors.white70)),
                  const Divider(color: Colors.white24, height: AppSpacing.xl),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Security Deposit', style: AppTypography.caption.copyWith(color: Colors.white60)),
                          Text('₹10,000 (Paid)', style: AppTypography.titleSmall.copyWith(color: AppColors.success)),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('Electricity Bill', style: AppTypography.caption.copyWith(color: Colors.white60)),
                          Text('₹450 (Paid)', style: AppTypography.titleSmall.copyWith(color: AppColors.success)),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Peer-to-Peer Payment Transfer Details Box
            const SectionHeader(title: 'Peer-to-Peer Payment Methods'),
            CustomCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 60,
                        height: 60,
                        decoration: BoxDecoration(
                          color: AppColors.background,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.divider),
                        ),
                        child: const Center(
                          child: Icon(Icons.qr_code_2_rounded, size: 36, color: AppColors.primary),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Rudra PG Official UPI ID', style: AppTypography.titleSmall),
                            const SizedBox(height: 2),
                            SelectableText('rudragrouppg@upi', style: AppTypography.bodyMedium.copyWith(color: AppColors.secondary, fontWeight: FontWeight.bold)),
                            Text('Accepted: GPay, PhonePe, Paytm, BHIM', style: AppTypography.caption),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    'Note: After transferring cash or UPI, take a screenshot and submit the reference number below for Sub Admin verification.',
                    style: AppTypography.caption,
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Upload Proof CTA
            CustomButton(
              text: 'Upload Payment Proof Screenshot',
              icon: Icons.upload_file_rounded,
              type: CustomButtonType.secondary,
              onPressed: () => _showUploadProofSheet(context),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Payment History List
            const SectionHeader(title: 'Payment Ledger History'),
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: payments.length,
              separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
              itemBuilder: (context, index) {
                return PaymentCard(payment: payments[index]);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showUploadProofSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppSpacing.radiusBottomSheet)),
      ),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            left: AppSpacing.xl,
            right: AppSpacing.xl,
            top: AppSpacing.xl,
            bottom: MediaQuery.of(context).viewInsets.bottom + AppSpacing.xl,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Submit Payment Proof', style: AppTypography.titleLarge),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              DropdownButtonFormField<String>(
                initialValue: 'August Rent (₹6,500)',
                decoration: const InputDecoration(labelText: 'Payment For'),
                items: const [
                  DropdownMenuItem(value: 'August Rent (₹6,500)', child: Text('August Rent (₹6,500)')),
                  DropdownMenuItem(value: 'Security Deposit (₹10,000)', child: Text('Security Deposit (₹10,000)')),
                  DropdownMenuItem(value: 'Electricity Bill', child: Text('Electricity Bill')),
                ],
                onChanged: (_) {},
              ),
              const SizedBox(height: AppSpacing.md),
              TextFormField(
                decoration: const InputDecoration(
                  labelText: 'Transaction UTR / Reference No.',
                  hintText: 'e.g. UPI/998877665544',
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Container(
                height: 100,
                width: double.infinity,
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(AppSpacing.radiusInput),
                  border: Border.all(color: AppColors.divider, style: BorderStyle.solid),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.cloud_upload_outlined, color: AppColors.secondary, size: 32),
                    const SizedBox(height: 4),
                    Text('Tap to pick payment screenshot', style: AppTypography.bodySmall),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
              CustomButton(
                text: 'Submit Proof for Verification',
                onPressed: () {
                  Navigator.of(context).pop();
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Payment proof submitted to Sub Admin for verification!')),
                  );
                },
              ),
            ],
          ),
        );
      },
    );
  }
}
