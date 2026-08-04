import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import 'data/student_repository.dart';
import '../electricity/electricity_screen.dart';
import '../payments/payments_screen.dart';
import '../resident/my_room_screen.dart';
import '../support/support_screen.dart';

class HomeDashboardScreen extends ConsumerWidget {
  const HomeDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(studentProfileProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: profileAsync.when(
          data: (resident) => SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Bar Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.verified_user_rounded, size: 16, color: AppColors.success),
                            const SizedBox(width: 4),
                            Text(
                              'APPROVED RESIDENT',
                              style: AppTypography.caption.copyWith(
                                color: AppColors.success,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 1.0,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          resident.branchName,
                          style: AppTypography.titleLarge,
                        ),
                      ],
                    ),
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        shape: BoxShape.circle,
                        boxShadow: AppSpacing.softShadow,
                      ),
                      child: Center(
                        child: Text(
                          resident.fullName.isNotEmpty ? resident.fullName[0].toUpperCase() : 'S',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.lg),

                // Resident Hero Welcome Card
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
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              'ROOM ${resident.roomNumber} • ${resident.bedCode}',
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
                        'Welcome, ${resident.fullName} 👋',
                        style: AppTypography.displayMedium.copyWith(color: Colors.white),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${resident.branchName} • Joining Date: ${resident.joiningDate}',
                        style: AppTypography.bodyMedium.copyWith(color: Colors.white70),
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      const Divider(color: Colors.white24),
                      const SizedBox(height: AppSpacing.md),

                      // Stay Status Indicators
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _buildStatusColumn(
                            label: 'Rent Payment',
                            value: resident.rentStatus,
                            color: resident.rentStatus.toLowerCase() == 'unpaid' ? AppColors.warning : AppColors.success,
                          ),
                          _buildStatusColumn(
                            label: 'Deposit Status',
                            value: resident.depositStatus,
                            color: AppColors.accent,
                          ),
                          _buildStatusColumn(
                            label: 'KYC Status',
                            value: resident.kycStatus,
                            color: AppColors.accent,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),

                // Quick Actions Grid (4 Items)
                const SectionHeader(title: 'Resident Services'),
                Row(
                  children: [
                    _buildQuickAction(
                      context,
                      icon: Icons.account_balance_wallet_rounded,
                      label: 'Pay Rent',
                      color: AppColors.secondary,
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const PaymentsScreen()),
                        );
                      },
                    ),
                    const SizedBox(width: AppSpacing.md),
                    _buildQuickAction(
                      context,
                      icon: Icons.bed_rounded,
                      label: 'My Room',
                      color: AppColors.accent,
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const MyRoomScreen()),
                        );
                      },
                    ),
                    const SizedBox(width: AppSpacing.md),
                    _buildQuickAction(
                      context,
                      icon: Icons.bolt_rounded,
                      label: 'Electricity',
                      color: AppColors.warning,
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const ElectricityScreen()),
                        );
                      },
                    ),
                    const SizedBox(width: AppSpacing.md),
                    _buildQuickAction(
                      context,
                      icon: Icons.headset_mic_rounded,
                      label: 'Support',
                      color: AppColors.success,
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const SupportScreen()),
                        );
                      },
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.xxl),

                // Assigned Stay Overview Snapshot Card
                const SectionHeader(title: 'My Stay Overview'),
                CustomCard(
                  child: Column(
                    children: [
                      _buildOverviewRow(
                        icon: Icons.location_on_outlined,
                        label: 'Branch Address',
                        value: resident.branchName, // In reality fetched from API
                      ),
                      const Divider(height: 24),
                      _buildOverviewRow(
                        icon: Icons.layers_outlined,
                        label: 'Room Floor & Type',
                        value: 'Room ${resident.roomNumber}',
                      ),
                      const Divider(height: 24),
                      _buildOverviewRow(
                        icon: Icons.support_agent_rounded,
                        label: 'Branch Manager',
                        value: 'Support Team',
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
          error: (error, stackTrace) => Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, color: Colors.red, size: 48),
                const SizedBox(height: 16),
                Text('Failed to load profile', style: AppTypography.titleMedium),
                const SizedBox(height: 8),
                Text(error.toString(), style: AppTypography.bodySmall, textAlign: TextAlign.center),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () => ref.refresh(studentProfileProvider),
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusColumn({
    required String label,
    required String value,
    required Color color,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: AppTypography.caption.copyWith(color: Colors.white60),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: AppTypography.bodySmall.copyWith(
            color: color,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickAction(
    BuildContext context, {
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: CustomCard(
        onTap: onTap,
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: 6),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: AppTypography.caption.copyWith(fontWeight: FontWeight.w600),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOverviewRow({
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
