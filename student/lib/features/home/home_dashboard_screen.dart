import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/room_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/dummy/dummy_data.dart';
import '../branch/branch_details_screen.dart';
import '../rooms/room_details_screen.dart';
import '../rooms/room_listing_screen.dart';
import '../electricity/electricity_screen.dart';
import '../resident/my_room_screen.dart';
import '../payments/payments_screen.dart';

class HomeDashboardScreen extends StatelessWidget {
  const HomeDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final branch = DummyData.activeBranch;
    final resident = DummyData.sampleResident;
    final rooms = DummyData.sampleRooms;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
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
                          const Icon(Icons.qr_code_2_rounded, size: 18, color: AppColors.secondary),
                          const SizedBox(width: 4),
                          Text(
                            'BRANCH DETECTED',
                            style: AppTypography.caption.copyWith(
                              color: AppColors.secondary,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        branch.name,
                        style: AppTypography.titleLarge,
                      ),
                    ],
                  ),
                  GestureDetector(
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const BranchDetailsScreen()),
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.divider),
                        boxShadow: AppSpacing.softShadow,
                      ),
                      child: const Icon(Icons.info_outline_rounded, color: AppColors.primary),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),

              // Resident Companion Active Stay Card
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
                            'ACTIVE RESIDENT',
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
                      'Hello, ${resident.fullName} 👋',
                      style: AppTypography.displayMedium.copyWith(color: Colors.white),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${resident.branchName} • Room ${resident.roomNumber} (${resident.bedCode})',
                      style: AppTypography.bodyMedium.copyWith(color: Colors.white70),
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.secondary,
                              foregroundColor: Colors.white,
                              minimumSize: const Size.fromHeight(44),
                            ),
                            onPressed: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(builder: (context) => const MyRoomScreen()),
                              );
                            },
                            icon: const Icon(Icons.bed_rounded, size: 18),
                            label: const Text('My Room'),
                          ),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: Colors.white,
                              side: const BorderSide(color: Colors.white38),
                              minimumSize: const Size.fromHeight(44),
                            ),
                            onPressed: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(builder: (context) => const ElectricityScreen()),
                              );
                            },
                            icon: const Icon(Icons.bolt_rounded, size: 18),
                            label: const Text('Meter Reading'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Quick Actions Grid
              const SectionHeader(title: 'Quick Actions'),
              Row(
                children: [
                  _buildQuickAction(
                    context,
                    icon: Icons.meeting_room_rounded,
                    label: 'View Rooms',
                    color: AppColors.secondary,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const RoomListingScreen()),
                      );
                    },
                  ),
                  const SizedBox(width: AppSpacing.md),
                  _buildQuickAction(
                    context,
                    icon: Icons.payment_rounded,
                    label: 'Pay Rent',
                    color: AppColors.success,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const PaymentsScreen()),
                      );
                    },
                  ),
                  const SizedBox(width: AppSpacing.md),
                  _buildQuickAction(
                    context,
                    icon: Icons.electric_meter_rounded,
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
                    icon: Icons.location_on_rounded,
                    label: 'Branch Info',
                    color: AppColors.accent,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const BranchDetailsScreen()),
                      );
                    },
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Outstanding Rent Due Reminder Card
              CustomCard(
                backgroundColor: AppColors.warning.withValues(alpha: 0.08),
                border: Border.all(color: AppColors.warning.withValues(alpha: 0.3)),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.warning.withValues(alpha: 0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.notifications_active_rounded, color: AppColors.warning),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('August Rent Notice', style: AppTypography.titleSmall),
                          const SizedBox(height: 2),
                          Text('₹6,500 due on 5th Aug 2026', style: AppTypography.bodySmall),
                        ],
                      ),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const PaymentsScreen()),
                        );
                      },
                      child: const Text('Pay Now'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Available Rooms Section
              SectionHeader(
                title: 'Available Rooms',
                actionText: 'See All',
                onActionTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (context) => const RoomListingScreen()),
                  );
                },
              ),
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: rooms.length,
                separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.lg),
                itemBuilder: (context, index) {
                  final room = rooms[index];
                  return RoomCard(
                    room: room,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (context) => RoomDetailsScreen(room: room),
                        ),
                      );
                    },
                  );
                },
              ),
            ],
          ),
        ),
      ),
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
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: 8),
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
}
