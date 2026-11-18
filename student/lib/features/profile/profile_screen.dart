import 'dart:async';
import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_card.dart';
import '../electricity/electricity_screen.dart';
import '../login/login_screen.dart';
import '../auth/presentation/auth_notifier.dart';
import '../resident/my_room_screen.dart';
import '../settings/settings_screen.dart';
import '../support/support_screen.dart';
import 'widgets/change_password_dialog.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../home/data/student_repository.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  Timer? _realtimeTimer;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.invalidate(studentProfileProvider);
    });
    // Real-time polling every 12 seconds without user refresh or re-login
    _realtimeTimer = Timer.periodic(const Duration(seconds: 12), (_) {
      if (mounted) {
        ref.invalidate(studentProfileProvider);
      }
    });
  }

  @override
  void dispose() {
    _realtimeTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final profileAsync = ref.watch(studentProfileProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Resident Profile'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: profileAsync.when(
          data: (resident) => SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Profile Header Card
                CustomCard(
                  padding: const EdgeInsets.all(AppSpacing.xl),
                  child: Row(
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                          boxShadow: AppSpacing.softShadow,
                        ),
                        child: Center(
                          child: Text(
                            resident.fullName.isNotEmpty ? resident.fullName[0].toUpperCase() : 'S',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 22),
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.lg),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              resident.fullName.isNotEmpty ? resident.fullName : 'Resident',
                              style: AppTypography.titleLarge,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 2),
                            Text(
                              resident.branchName,
                              style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppColors.success.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                '✓ ${resident.kycStatus.toUpperCase()}',
                                style: AppTypography.caption.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),

              // Personal Information Section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Personal Information', style: AppTypography.titleLarge),
                  TextButton.icon(
                    icon: const Icon(Icons.edit_note_rounded, size: 18),
                    label: const Text('Edit Details'),
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      textStyle: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    onPressed: () => _showEditProfileDialog(context, resident),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildInfoTile(icon: Icons.calendar_month_rounded, label: 'Joining Date', value: resident.joiningDate.isNotEmpty ? resident.joiningDate : 'N/A'),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.phone_android_rounded, label: 'Mobile Number', value: resident.phone),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.email_outlined, label: 'Email Address', value: resident.email),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.badge_outlined, label: 'Aadhaar Number', value: resident.aadhaarNumber),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.credit_card_rounded, label: 'PAN Card Number', value: resident.panNumber),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Parent & Emergency Details
              Text('Parent & Emergency Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildInfoTile(icon: Icons.family_restroom_rounded, label: 'Parent / Guardian', value: resident.parentName),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.phone_callback_rounded, label: 'Parent Phone', value: resident.parentPhone),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.contact_phone_outlined, label: 'Emergency Contact', value: resident.emergencyContact),
                    const Divider(height: 20),
                    _buildInfoTile(icon: Icons.location_on_outlined, label: 'Permanent Address', value: resident.currentAddress),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Navigation Quick Links
              Text('Resident Management', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomCard(
                child: Column(
                  children: [
                    _buildNavTile(
                      icon: Icons.bed_rounded,
                      title: 'My Room Details',
                      subtitle: 'View assigned floor, bed & manager contact',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const MyRoomScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.electric_meter_rounded,
                      title: 'Electricity Meter Readings',
                      subtitle: 'View consumption & submit meter photo',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const ElectricityScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.headset_mic_rounded,
                      title: 'Support & Complaints Desk',
                      subtitle: 'Call manager, WhatsApp support & raise tickets',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const SupportScreen()),
                        );
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.lock_reset_rounded,
                      title: 'Change Password',
                      subtitle: 'Update your account login password',
                      onTap: () {
                        ChangePasswordDialog.show(context);
                      },
                    ),
                    const Divider(height: 20),
                    _buildNavTile(
                      icon: Icons.settings_outlined,
                      title: 'App Settings & Policy',
                      subtitle: 'Privacy policy, terms & help desk',
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const SettingsScreen()),
                        );
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Logout Button
              CustomCard(
                onTap: () => _confirmSignOut(context, ref),
                backgroundColor: AppColors.error.withValues(alpha: 0.05),
                border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.logout_rounded, color: AppColors.error, size: 20),
                    const SizedBox(width: AppSpacing.sm),
                    Flexible(
                      child: Text(
                        'Sign Out of Resident Account',
                        style: AppTypography.titleSmall.copyWith(color: AppColors.error, fontWeight: FontWeight.bold),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error loading profile: $err')),
      ),
    ),
    );
  }

  Widget _buildInfoTile({
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

  Widget _buildNavTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: AppColors.primary, size: 20),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: AppTypography.titleSmall),
                  const SizedBox(height: 2),
                  Text(subtitle, style: AppTypography.caption),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }

  void _confirmSignOut(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign Out'),
        content: const Text('Are you sure you want to sign out of your resident companion portal?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
            onPressed: () async {
              Navigator.pop(context);
              await ref.read(authNotifierProvider.notifier).logout();
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                  (route) => false,
                );
              }
            },
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }

  void _showEditProfileDialog(BuildContext context, ResidentModel resident) {
    final nameCtrl = TextEditingController(text: resident.fullName);
    final emailCtrl = TextEditingController(text: resident.email);
    final parentNameCtrl = TextEditingController(text: resident.parentName);
    final parentPhoneCtrl = TextEditingController(text: resident.parentPhone);
    final emergencyCtrl = TextEditingController(text: resident.emergencyContact);
    final addressCtrl = TextEditingController(text: resident.currentAddress);
    final panCtrl = TextEditingController(text: resident.panNumber);
    final joiningCtrl = TextEditingController(text: resident.joiningDate != 'N/A' ? resident.joiningDate : '');

    DateTime? selectedJoiningDate;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.only(
            top: 20,
            left: 20,
            right: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(ctx).size.height * 0.85,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.edit_note_rounded, color: AppColors.primary, size: 22),
                          ),
                          const SizedBox(width: 10),
                          const Text(
                            'Edit Profile Details',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text('Personal Information', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.primary)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: nameCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Full Legal Name',
                      prefixIcon: Icon(Icons.person_outline_rounded, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: emailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(
                      labelText: 'Email Address',
                      prefixIcon: Icon(Icons.email_outlined, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: joiningCtrl,
                    readOnly: true,
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: ctx,
                        initialDate: selectedJoiningDate ?? DateTime.now(),
                        firstDate: DateTime(2020),
                        lastDate: DateTime(2035),
                      );
                      if (picked != null) {
                        setModalState(() {
                          selectedJoiningDate = picked;
                          joiningCtrl.text = "${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}";
                        });
                      }
                    },
                    decoration: const InputDecoration(
                      labelText: 'Joining / Move-in Date',
                      hintText: 'Tap to pick date',
                      prefixIcon: Icon(Icons.calendar_month_rounded, size: 20),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Parent & Emergency Contact', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.primary)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: parentNameCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Parent / Guardian Name',
                      prefixIcon: Icon(Icons.family_restroom_rounded, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: parentPhoneCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Parent Phone Number',
                      prefixIcon: Icon(Icons.phone_callback_rounded, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: emergencyCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Emergency Contact Number',
                      prefixIcon: Icon(Icons.contact_phone_outlined, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: panCtrl,
                    textCapitalization: TextCapitalization.characters,
                    decoration: const InputDecoration(
                      labelText: 'PAN Card Number',
                      prefixIcon: Icon(Icons.credit_card_rounded, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: addressCtrl,
                    maxLines: 2,
                    decoration: const InputDecoration(
                      labelText: 'Permanent Address',
                      prefixIcon: Icon(Icons.location_on_outlined, size: 20),
                    ),
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: isSaving ? null : () async {
                        setModalState(() => isSaving = true);
                        try {
                          final payload = <String, dynamic>{
                            'full_name': nameCtrl.text.trim(),
                            'email': emailCtrl.text.trim(),
                            'parent_name': parentNameCtrl.text.trim(),
                            'parent_phone': parentPhoneCtrl.text.trim(),
                            'emergency_contact': emergencyCtrl.text.trim(),
                            'current_address': addressCtrl.text.trim(),
                            'pan_number': panCtrl.text.trim().toUpperCase(),
                          };
                          if (selectedJoiningDate != null) {
                            payload['joining_date'] =
                                "${selectedJoiningDate!.year}-${selectedJoiningDate!.month.toString().padLeft(2, '0')}-${selectedJoiningDate!.day.toString().padLeft(2, '0')}";
                          }
                          await ref.read(studentRepositoryProvider).updateProfile(payload);
                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                          }
                          ref.invalidate(studentProfileProvider);
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                backgroundColor: AppColors.success,
                                content: Text('✓ Profile details updated successfully!'),
                              ),
                            );
                          }
                        } catch (err) {
                          setModalState(() => isSaving = false);
                          if (ctx.mounted) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              SnackBar(
                                backgroundColor: AppColors.error,
                                content: Text(err.toString().replaceAll('Exception: ', '')),
                              ),
                            );
                          }
                        }
                      },
                      child: isSaving
                          ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Text('Save Changes', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
