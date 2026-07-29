import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../core/widgets/section_header.dart';
import '../../core/widgets/status_badge.dart';
import '../../data/dummy/dummy_data.dart';

class ElectricityScreen extends StatefulWidget {
  const ElectricityScreen({super.key});

  @override
  State<ElectricityScreen> createState() => _ElectricityScreenState();
}

class _ElectricityScreenState extends State<ElectricityScreen> {
  bool _photoCaptured = true;

  @override
  Widget build(BuildContext context) {
    final history = DummyData.electricityHistory;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Monthly Electricity Reading'),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Current Rate Info Banner
            CustomCard(
              backgroundColor: AppColors.secondary.withValues(alpha: 0.08),
              border: Border.all(color: AppColors.secondary.withValues(alpha: 0.3)),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.secondary.withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.bolt_rounded, color: AppColors.secondary, size: 24),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Naroda Branch Tariff Rate', style: AppTypography.titleSmall),
                        Text('₹10.00 / Electricity Unit', style: AppTypography.bodySmall),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Monthly Reading Submission Form
            const SectionHeader(title: 'August Reading Submission'),
            const CustomTextField(
              label: 'Current Meter Reading (kWh)',
              hint: 'e.g. 14565.50',
              initialValue: '14565.50',
              keyboardType: TextInputType.numberWithOptions(decimal: true),
              prefixIcon: Icons.speed_rounded,
            ),
            const SizedBox(height: AppSpacing.lg),

            // Camera Attachment UI
            Text('Meter Photograph Proof', style: AppTypography.titleSmall),
            const SizedBox(height: AppSpacing.sm),
            CustomCard(
              onTap: () {
                setState(() => _photoCaptured = !_photoCaptured);
              },
              backgroundColor: AppColors.background,
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _photoCaptured ? Icons.check_circle_rounded : Icons.camera_alt_rounded,
                    color: _photoCaptured ? AppColors.success : AppColors.secondary,
                    size: 28,
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _photoCaptured ? 'Meter Photo Captured (meter_reading.jpg)' : 'Take Photo of Physical Meter',
                        style: AppTypography.titleSmall,
                      ),
                      Text('Clear view of digits required', style: AppTypography.caption),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            const CustomTextField(
              label: 'Remarks (Optional)',
              hint: 'Any meter fluctuation notes...',
              prefixIcon: Icons.notes_rounded,
            ),
            const SizedBox(height: AppSpacing.xl),

            CustomButton(
              text: 'Submit Meter Reading',
              icon: Icons.send_rounded,
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Meter reading submitted for Sub Admin verification!')),
                );
              },
            ),
            const SizedBox(height: AppSpacing.xxxl),

            // Submission History Timeline
            const SectionHeader(title: 'Previous Reading History'),
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: history.length,
              separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
              itemBuilder: (context, index) {
                final item = history[index];
                return CustomCard(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item.month, style: AppTypography.titleSmall),
                          const SizedBox(height: 2),
                          Text('${item.unitsConsumed.toInt()} Units • Reading: ${item.readingValue.toInt()}', style: AppTypography.bodySmall),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('₹${item.totalAmount.toInt()}', style: AppTypography.titleMedium.copyWith(color: AppColors.primary)),
                          const SizedBox(height: 4),
                          StatusBadge.verified(),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
