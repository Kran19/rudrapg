import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../home/data/student_repository.dart';

class ElectricityScreen extends ConsumerStatefulWidget {
  const ElectricityScreen({super.key});

  @override
  ConsumerState<ElectricityScreen> createState() => _ElectricityScreenState();
}

class _ElectricityScreenState extends ConsumerState<ElectricityScreen> {
  final _readingController = TextEditingController();
  bool _isPhotoAttached = false;
  bool _isSubmitting = false;

  @override
  void dispose() {
    _readingController.dispose();
    super.dispose();
  }

  Future<void> _submitReading() async {
    final reading = _readingController.text.trim();
    if (reading.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter current reading.')));
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      await ref.read(studentRepositoryProvider).submitElectricityReading({
        'current_reading': reading,
        'meter_photo_path': _isPhotoAttached ? 'uploads/meter/dummy_photo.jpg' : null,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reading submitted! Sub Admin will audit.')));
        _readingController.clear();
        setState(() {
          _isPhotoAttached = false;
        });
        ref.invalidate(electricityHistoryProvider);
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
    final historyAsync = ref.watch(electricityHistoryProvider);
    final profileAsync = ref.watch(studentProfileProvider);

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
              profileAsync.when(
                data: (profile) {
                  // If we had the real previous reading, we would compute it here.
                  // For now, we just display static info based on profile or 0 if history is empty.
                  return CustomCard(
                    backgroundColor: AppColors.primary,
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'SUB-METER OVERVIEW',
                              style: AppTypography.caption.copyWith(color: Colors.white70, letterSpacing: 1.0),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppSpacing.md),
                        Text(
                          'Room ${profile.roomNumber}',
                          style: AppTypography.displayMedium.copyWith(color: Colors.white),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Branch: ${profile.branchName}',
                          style: AppTypography.bodySmall.copyWith(color: Colors.white70),
                        ),
                      ],
                    ),
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => const SizedBox(),
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
                      isLoading: _isSubmitting,
                      onPressed: _submitReading,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Reading History Logs
              Text('Meter Reading History', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              historyAsync.when(
                data: (history) {
                  if (history.isEmpty) {
                    return const Text('No history found.');
                  }
                  return ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: history.length,
                    separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, index) {
                      final item = history[index];
                      final amount = (double.tryParse(item['total_amount']?.toString() ?? '0') ?? 0).toInt();
                      return CustomCard(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(item['reading_month'] ?? 'Month', style: AppTypography.titleSmall),
                                const SizedBox(height: 2),
                                Text('Reading: ${item['current_reading']} kWh (${item['units_consumed']} Units)', style: AppTypography.bodySmall),
                              ],
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
                error: (err, stack) => Text('Error loading history: $err'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
