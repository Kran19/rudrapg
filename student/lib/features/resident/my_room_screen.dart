import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../home/data/student_repository.dart';

class MyRoomScreen extends ConsumerWidget {
  const MyRoomScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(studentProfileProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Assigned Room'),
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
              // Room Hero Banner Card
              profileAsync.when(
                data: (resident) => Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
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
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: AppColors.accent.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: Text(
                                  'ASSIGNED SPOT',
                                  style: AppTypography.badge.copyWith(color: AppColors.accent),
                                ),
                              ),
                              Text(
                                '₹${resident.monthlyRent.toInt()}/mo',
                                style: AppTypography.titleMedium.copyWith(color: Colors.white),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Text(
                            'Room ${resident.roomNumber} (${resident.bedCode})',
                            style: AppTypography.displayMedium.copyWith(color: Colors.white),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Floor ${resident.floorNumber} • ${resident.sharingType}',
                            style: AppTypography.bodyMedium.copyWith(color: Colors.white70),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xxl),

                    // Room Specifications Card (Read-Only)
                    Text('Room Specifications', style: AppTypography.titleLarge),
                    const SizedBox(height: AppSpacing.md),
                    CustomCard(
                      child: Column(
                        children: [
                          _buildSpecRow(
                            icon: Icons.location_city_rounded,
                            label: 'PG Branch',
                            value: resident.branchName,
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.layers_rounded,
                            label: 'Floor Number',
                            value: 'Floor ${resident.floorNumber}',
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.meeting_room_rounded,
                            label: 'Room Number',
                            value: resident.roomNumber,
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.single_bed_rounded,
                            label: 'Assigned Bed',
                            value: resident.bedCode,
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.ac_unit_rounded,
                            label: 'Sharing Category',
                            value: resident.sharingType,
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.calendar_today_rounded,
                            label: 'Joining Date',
                            value: resident.joiningDate,
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.payments_rounded,
                            label: 'Monthly Rent',
                            value: '₹${resident.monthlyRent.toInt()}',
                          ),
                          const Divider(height: 24),
                          _buildSpecRow(
                            icon: Icons.verified_user_rounded,
                            label: 'Security Deposit',
                            value: '₹${resident.securityDeposit.toInt()} (Verified & Held)',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xxl),

                    // Branch Manager Contact Card
                    Text('Branch Manager Assistance', style: AppTypography.titleLarge),
                    const SizedBox(height: AppSpacing.md),
                    CustomCard(
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.secondary.withValues(alpha: 0.12),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.support_agent_rounded, color: AppColors.secondary, size: 28),
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Manager', style: AppTypography.titleSmall),
                                const SizedBox(height: 2),
                                Text('Branch Manager', style: AppTypography.bodySmall),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.phone_rounded, color: AppColors.success),
                            onPressed: () {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Calling branch manager...')),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => const SizedBox(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSpecRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.secondary),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: AppTypography.caption.copyWith(color: AppColors.textSecondary)),
              const SizedBox(height: 2),
              Text(value, style: AppTypography.bodyMedium.copyWith(fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ],
    );
  }
}
