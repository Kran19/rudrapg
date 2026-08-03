import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../registration_submitted/registration_submitted_screen.dart';

class StudentRegistrationScreen extends StatefulWidget {
  const StudentRegistrationScreen({super.key});

  @override
  State<StudentRegistrationScreen> createState() => _StudentRegistrationScreenState();
}

class _StudentRegistrationScreenState extends State<StudentRegistrationScreen> {
  final _fullNameController = TextEditingController(text: 'Rahul Sharma');
  final _phoneController = TextEditingController(text: '+91 98765 43210');
  final _emailController = TextEditingController(text: 'rahul.sharma@gmail.com');
  final _aadhaarController = TextEditingController(text: '9912-3456-7890');
  final _panController = TextEditingController(text: 'ABCDE1234F');
  final _parentNameController = TextEditingController(text: 'Sanjay Sharma');
  final _parentPhoneController = TextEditingController(text: '+91 98250 11223');
  final _addressController = TextEditingController(text: '102, Shanti Nagar, SG Highway, Ahmedabad');

  bool _isProfileUploaded = true;
  bool _isAadhaarUploaded = true;
  bool _isPanUploaded = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Resident Registration'),
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
              // Branch Locked Info Card
              CustomCard(
                backgroundColor: AppColors.primary,
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withValues(alpha: 0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.qr_code_2_rounded, color: AppColors.accent, size: 24),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.accent.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  'BRANCH LOCKED',
                                  style: AppTypography.caption.copyWith(color: AppColors.accent, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Naroda Branch (PG-NRD-01)',
                            style: AppTypography.titleMedium.copyWith(color: Colors.white),
                          ),
                          Text(
                            '102, Main Highway Road, Naroda, Ahmedabad',
                            style: AppTypography.bodySmall.copyWith(color: Colors.white70),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Personal Information Section
              Text('Personal Details', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Full Name',
                hint: 'Enter your legal name',
                prefixIcon: Icons.person_outline_rounded,
                controller: _fullNameController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Mobile Number',
                hint: '+91 XXXXX XXXXX',
                prefixIcon: Icons.phone_android_rounded,
                keyboardType: TextInputType.phone,
                controller: _phoneController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Email Address',
                hint: 'name@domain.com',
                prefixIcon: Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
                controller: _emailController,
              ),
              const SizedBox(height: AppSpacing.xxl),

              // KYC Identification Documents Section
              Text('KYC Identification', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Aadhaar Card Number',
                hint: '12-digit Aadhaar Number',
                prefixIcon: Icons.badge_outlined,
                keyboardType: TextInputType.number,
                controller: _aadhaarController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'PAN Card Number',
                hint: '10-character PAN',
                prefixIcon: Icons.credit_card_rounded,
                controller: _panController,
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Parent & Guardian Section
              Text('Parent / Guardian Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Parent / Guardian Name',
                hint: 'Father or Guardian Full Name',
                prefixIcon: Icons.family_restroom_rounded,
                controller: _parentNameController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Parent / Guardian Phone',
                hint: '+91 XXXXX XXXXX',
                prefixIcon: Icons.phone_callback_rounded,
                keyboardType: TextInputType.phone,
                controller: _parentPhoneController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Permanent Address',
                hint: 'House No, Street, City, Pincode',
                prefixIcon: Icons.location_on_outlined,
                maxLines: 2,
                controller: _addressController,
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Document Upload Placeholders
              Text('Document Attachments', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Student Profile Passport Photo',
                isUploaded: _isProfileUploaded,
                icon: Icons.account_box_rounded,
                onTap: () {
                  setState(() {
                    _isProfileUploaded = !_isProfileUploaded;
                  });
                },
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Front & Back)',
                isUploaded: _isAadhaarUploaded,
                icon: Icons.file_present_rounded,
                onTap: () {
                  setState(() {
                    _isAadhaarUploaded = !_isAadhaarUploaded;
                  });
                },
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'PAN Card Attachment',
                isUploaded: _isPanUploaded,
                icon: Icons.badge_rounded,
                onTap: () {
                  setState(() {
                    _isPanUploaded = !_isPanUploaded;
                  });
                },
              ),
              const SizedBox(height: AppSpacing.xxxl),

              // Submit Action
              CustomButton(
                text: 'Submit Resident Registration',
                icon: Icons.arrow_forward_rounded,
                onPressed: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(
                      builder: (context) => const RegistrationSubmittedScreen(),
                    ),
                  );
                },
              ),
              const SizedBox(height: AppSpacing.xl),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildUploadCard({
    required String title,
    required bool isUploaded,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return CustomCard(
      onTap: onTap,
      backgroundColor: isUploaded ? AppColors.success.withValues(alpha: 0.05) : Colors.white,
      border: Border.all(
        color: isUploaded ? AppColors.success.withValues(alpha: 0.4) : AppColors.divider,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: (isUploaded ? AppColors.success : AppColors.secondary).withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(
              isUploaded ? Icons.check_circle_rounded : icon,
              color: isUploaded ? AppColors.success : AppColors.secondary,
              size: 22,
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: AppTypography.titleSmall),
                const SizedBox(height: 2),
                Text(
                  isUploaded ? 'File Attached (profile_doc.jpg)' : 'Tap to select document file',
                  style: AppTypography.bodySmall.copyWith(
                    color: isUploaded ? AppColors.success : AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          Icon(
            isUploaded ? Icons.edit_rounded : Icons.cloud_upload_outlined,
            color: isUploaded ? AppColors.success : AppColors.textSecondary,
            size: 20,
          ),
        ],
      ),
    );
  }
}
