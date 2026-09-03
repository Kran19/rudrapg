import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/models/resident_model.dart';
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
          data: (resident) => RefreshIndicator(
            onRefresh: () async {
              ref.invalidate(studentProfileProvider);
              ref.invalidate(paymentHistoryProvider);
            },
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
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
                              Icon(
                                resident.isFullyApproved
                                    ? Icons.verified_user_rounded
                                    : (resident.isRoomAssigned
                                        ? Icons.bed_rounded
                                        : Icons.pending_actions_rounded),
                                size: 16,
                                color: resident.isFullyApproved
                                    ? AppColors.success
                                    : (resident.isRoomAssigned
                                        ? AppColors.accent
                                        : AppColors.warning),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                resident.isFullyApproved
                                    ? 'APPROVED RESIDENT'
                                    : (resident.isPaymentSubmitted
                                        ? 'PAYMENT UNDER AUDIT'
                                        : (resident.isRoomAssigned
                                            ? 'BED ASSIGNED • PAY RENT'
                                            : (resident.isKycApproved
                                                ? 'KYC VERIFIED • AWAITING BED'
                                                : 'APPLICATION UNDER REVIEW'))),
                                style: AppTypography.caption.copyWith(
                                  color: resident.isFullyApproved
                                      ? AppColors.success
                                      : (resident.isRoomAssigned
                                          ? AppColors.accent
                                          : AppColors.warning),
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.8,
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
                                color: (resident.isRoomAssigned ? AppColors.accent : AppColors.warning).withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                resident.isRoomAssigned
                                    ? 'ROOM ${resident.roomNumber} • ${resident.bedCode}'
                                    : 'ROOM ALLOCATION PENDING',
                                style: AppTypography.badge.copyWith(
                                  color: resident.isRoomAssigned ? AppColors.accent : AppColors.warning,
                                ),
                              ),
                            ),
                            Text(
                              resident.isRoomAssigned ? '₹${resident.monthlyRent.toInt()}/mo' : 'Pending Allocation',
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
                          'Ref: ${resident.id} • Joining Date: ${resident.joiningDate}',
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
                              value: resident.isRoomAssigned ? resident.rentStatus : 'Not Applicable',
                              color: resident.isPaid
                                  ? AppColors.success
                                  : (resident.isPaymentSubmitted
                                      ? AppColors.accent
                                      : (resident.isRoomAssigned ? AppColors.warning : Colors.white60)),
                            ),
                            _buildStatusColumn(
                              label: 'Deposit Status',
                              value: resident.isRoomAssigned ? resident.depositStatus : 'Not Applicable',
                              color: resident.isPaid
                                  ? AppColors.success
                                  : (resident.isPaymentSubmitted ? AppColors.accent : (resident.isRoomAssigned ? AppColors.warning : Colors.white60)),
                            ),
                            _buildStatusColumn(
                              label: 'KYC Status',
                              value: resident.kycStatus,
                              color: resident.isKycApproved ? AppColors.success : AppColors.warning,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Real-Time Onboarding Milestone Tracker (Always visible during onboarding)
                  if (!resident.isFullyApproved) ...[
                    _buildOnboardingProgressCard(context, resident),
                    const SizedBox(height: AppSpacing.xxl),
                  ],

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
                          value: resident.branchName,
                        ),
                        const Divider(height: 24),
                        _buildOverviewRow(
                          icon: Icons.layers_outlined,
                          label: 'Room Floor & Type',
                          value: resident.isRoomAssigned ? 'Room ${resident.roomNumber} (${resident.sharingType})' : 'Pending Allocation',
                        ),
                        const Divider(height: 24),
                        _buildOverviewRow(
                          icon: Icons.support_agent_rounded,
                          label: 'Branch Manager Desk',
                          value: '${resident.emergencyContactName} • ${resident.emergencyContactPhone}',
                        ),
                      ],
                    ),
                  ),
                ],
              ),
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
                  onPressed: () => ref.invalidate(studentProfileProvider),
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildOnboardingProgressCard(BuildContext context, ResidentModel resident) {
    return CustomCard(
      backgroundColor: Colors.white,
      padding: const EdgeInsets.all(AppSpacing.xl),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: AppColors.secondary.withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.timeline_rounded, color: AppColors.secondary, size: 18),
                  ),
                  const SizedBox(width: 8),
                  Text('Onboarding Progress', style: AppTypography.titleMedium),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.secondary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Step ${resident.currentOnboardingStep} of 5',
                  style: AppTypography.caption.copyWith(color: AppColors.secondary, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),

          // Step 1: Application Submitted
          _buildMilestoneStep(
            stepNumber: 1,
            title: 'Application Submitted',
            subtitle: 'Ref #${resident.id} • Registered at ${resident.branchName}',
            isCompleted: true,
            isActive: false,
          ),

          // Step 2: KYC Document Audit
          _buildMilestoneStep(
            stepNumber: 2,
            title: 'KYC Document Verification',
            subtitle: resident.isKycApproved
                ? 'Aadhaar & PAN verified by Branch Admin ✓'
                : 'Branch Manager is reviewing your identity documents',
            isCompleted: resident.isKycApproved,
            isActive: !resident.isKycApproved,
          ),

          // Step 3: Room & Bed Assignment
          _buildMilestoneStep(
            stepNumber: 3,
            title: 'Room & Bed Allocation',
            subtitle: resident.isRoomAssigned
                ? 'Room ${resident.roomNumber} • Bed ${resident.bedCode} Assigned ✓'
                : (resident.isKycApproved
                    ? 'Branch Manager is selecting your bed in Room Map...'
                    : 'Locked (Awaiting Step 2 KYC Approval)'),
            isCompleted: resident.isRoomAssigned,
            isActive: resident.isKycApproved && !resident.isRoomAssigned,
          ),

          // Step 4: Rent & Deposit Payment
          _buildMilestoneStep(
            stepNumber: 4,
            title: 'Rent & Security Deposit Payment',
            subtitle: resident.isPaid
                ? 'Payment Verified & Cleared ✓'
                : (resident.isPaymentSubmitted
                    ? 'Payment Proof Submitted • Verification in Progress ⏳'
                    : (resident.isRoomAssigned
                        ? 'Total Due: ₹${(resident.monthlyRent + resident.securityDeposit).toInt()} (Rent ₹${resident.monthlyRent.toInt()} + Deposit ₹${resident.securityDeposit.toInt()})'
                        : 'Locked (Awaiting Step 3 Bed Allocation)')),
            isCompleted: resident.isPaid,
            isActive: resident.isRoomAssigned && !resident.isPaid,
            trailingAction: (resident.isRoomAssigned && !resident.isPaid && !resident.isPaymentSubmitted)
                ? ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.secondary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                    icon: const Icon(Icons.payment_rounded, size: 14),
                    label: const Text('Pay Now'),
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (context) => const PaymentsScreen()),
                      );
                    },
                  )
                : null,
          ),

          // Step 5: Admission Complete & Key Handover
          _buildMilestoneStep(
            stepNumber: 5,
            title: 'Key Handover & Resident Active',
            subtitle: resident.isFullyApproved
                ? 'Admission Complete • Welcome to Rudra PG! 🗝️'
                : 'Collect room keys from reception upon payment approval',
            isCompleted: resident.isFullyApproved,
            isActive: resident.isPaid && !resident.isFullyApproved,
            isLast: true,
          ),
        ],
      ),
    );
  }

  Widget _buildMilestoneStep({
    required int stepNumber,
    required String title,
    required String subtitle,
    required bool isCompleted,
    required bool isActive,
    bool isLast = false,
    Widget? trailingAction,
  }) {
    final Color iconColor = isCompleted
        ? AppColors.success
        : (isActive ? AppColors.warning : Colors.grey.shade400);

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: isCompleted
                    ? AppColors.success
                    : (isActive ? AppColors.warning.withValues(alpha: 0.2) : Colors.grey.shade200),
                shape: BoxShape.circle,
                border: Border.all(
                  color: iconColor,
                  width: 2,
                ),
              ),
              child: Center(
                child: isCompleted
                    ? const Icon(Icons.check, size: 16, color: Colors.white)
                    : Text(
                        '$stepNumber',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: isActive ? AppColors.warning : Colors.grey.shade600,
                        ),
                      ),
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 38,
                color: isCompleted ? AppColors.success : Colors.grey.shade300,
              ),
          ],
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: AppSpacing.sm),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: isCompleted
                        ? AppColors.textPrimary
                        : (isActive ? AppColors.primary : Colors.grey.shade600),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 11,
                    color: isCompleted
                        ? AppColors.textSecondary
                        : (isActive ? AppColors.warning : Colors.grey.shade500),
                  ),
                ),
                if (trailingAction != null) ...[
                  const SizedBox(height: 6),
                  trailingAction,
                ],
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatusColumn({required String label, required String value, required Color color}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AppTypography.caption.copyWith(color: Colors.white70)),
        const SizedBox(height: 2),
        Text(
          value.toUpperCase(),
          style: AppTypography.bodySmall.copyWith(color: color, fontWeight: FontWeight.bold),
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
      child: GestureDetector(
        onTap: onTap,
        child: CustomCard(
          padding: const EdgeInsets.symmetric(vertical: AppSpacing.lg, horizontal: AppSpacing.xs),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(height: AppSpacing.sm),
              Text(
                label,
                style: AppTypography.caption.copyWith(fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOverviewRow({required IconData icon, required String label, required String value}) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.secondary),
        const SizedBox(width: AppSpacing.md),
        Text(label, style: AppTypography.bodyMedium),
        const Spacer(),
        Text(value, style: AppTypography.titleSmall),
      ],
    );
  }
}
