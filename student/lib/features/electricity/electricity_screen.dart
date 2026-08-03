import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../data/dummy/dummy_data.dart';

class ElectricityScreen extends StatefulWidget {
  const ElectricityScreen({super.key});

  @override
  State<ElectricityScreen> createState() => _ElectricityScreenState();
}

class _ElectricityScreenState extends State<ElectricityScreen> {
  final _readingController = TextEditingController();
  bool _isPhotoAttached = false;

  @override
  Widget build(BuildContext context) {
    final elec = DummyData.electricityData;
    final history = elec['history'] as List<Map<String, dynamic>>;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Electricity Sub-Meter Readings'),
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
              // Current Reading Summary Card
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
                          'SUB-METER READING (JULY 2026)',
                          style: AppTypography.caption.copyWith(color: Colors.white70, letterSpacing: 1.0),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.success.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            '✓ APPROVED',
                            style: AppTypography.badge.copyWith(color: AppColors.success),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.md),
                    Text(
                      '${elec['currReading']} kWh',
                      style: AppTypography.displayMedium.copyWith(color: Colors.white),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Previous Reading: ${elec['prevReading']} kWh • Units: ${elec['unitsConsumed']} Units',
                      style: AppTypography.bodySmall.copyWith(color: Colors.white70),
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    const Divider(color: Colors.white24),
                    const SizedBox(height: AppSpacing.md),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Tariff Rate: ₹${elec['unitRate'].toInt()}/unit', style: AppTypography.bodySmall.copyWith(color: Colors.white)),
                        Text(
                          'Total Bill: ₹${elec['totalAmount'].toInt()}',
                          style: AppTypography.titleMedium.copyWith(color: AppColors.accent, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Upload New Reading Card
              Text('Submit Monthly Reading', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CustomTextField(
                      label: 'Current Sub-Meter Reading (kWh)',
                      hint: 'Enter 5-digit meter reading',
                      prefixIcon: Icons.electric_meter_rounded,
                      keyboardType: TextInputType.number,
                      controller: _readingController,
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomCard(
                      onTap: () {
                        setState(() {
                          _isPhotoAttached = !_isPhotoAttached;
                        });
                      },
                      backgroundColor: _isPhotoAttached ? AppColors.success.withValues(alpha: 0.05) : Colors.white,
                      border: Border.all(color: _isPhotoAttached ? AppColors.success : AppColors.divider),
                      child: Row(
                        children: [
                          Icon(
                            _isPhotoAttached ? Icons.check_circle_rounded : Icons.camera_alt_outlined,
                            color: _isPhotoAttached ? AppColors.success : AppColors.secondary,
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Physical Meter Photo Snapshot', style: AppTypography.titleSmall),
                                const SizedBox(height: 2),
                                Text(
                                  _isPhotoAttached ? 'meter_photo_july.jpg Attached' : 'Tap to capture or upload meter photo',
                                  style: AppTypography.bodySmall.copyWith(
                                    color: _isPhotoAttached ? AppColors.success : AppColors.textSecondary,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.lg),

                    CustomButton(
                      text: 'Submit Reading for Audit',
                      icon: Icons.send_rounded,
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Reading submitted! Sub Admin will audit and approve.')),
                        );
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Reading History Logs
              Text('Meter Reading History', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
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
                            Text(item['month'], style: AppTypography.titleSmall),
                            const SizedBox(height: 2),
                            Text('Reading: ${item['reading']} kWh (${item['units']} Units)', style: AppTypography.bodySmall),
                          ],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              '₹${item['amount'].toInt()}',
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
                                item['status'],
                                style: AppTypography.caption.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                              ),
                            ),
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
      ),
    );
  }
}
