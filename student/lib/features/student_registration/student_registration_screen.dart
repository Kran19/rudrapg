import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../registration_submitted/registration_submitted_screen.dart';
import '../home/data/student_repository.dart';
import 'package:flutter/foundation.dart';

class StudentRegistrationScreen extends ConsumerStatefulWidget {
  const StudentRegistrationScreen({super.key});

  @override
  ConsumerState<StudentRegistrationScreen> createState() => _StudentRegistrationScreenState();
}

class _StudentRegistrationScreenState extends ConsumerState<StudentRegistrationScreen> {
  final _fullNameController = TextEditingController(text: 'Rahul Sharma');
  final _phoneController = TextEditingController(text: '+91 98765 43210');
  final _emailController = TextEditingController(text: 'rahul.sharma@gmail.com');
  final _aadhaarController = TextEditingController(text: '9912-3456-7890');
  final _panController = TextEditingController(text: 'ABCDE1234F');
  final _parentNameController = TextEditingController(text: 'Sanjay Sharma');
  final _parentPhoneController = TextEditingController(text: '+91 98250 11223');
  final _addressController = TextEditingController(text: '102, Shanti Nagar, SG Highway, Ahmedabad');

  XFile? _profilePhoto;
  XFile? _aadhaarFront;
  XFile? _aadhaarBack;
  XFile? _panCard;

  bool _isLoading = false;

  Future<void> _pickImage(ImageSource source, void Function(XFile?) onPicked) async {
    if (!kIsWeb) {
      final permission = source == ImageSource.camera ? Permission.camera : Permission.photos;
      final status = await permission.request();

      if (!status.isGranted) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Permission required to access ${source == ImageSource.camera ? "camera" : "gallery"}')),
          );
        }
        return;
      }
    }
    
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: source, imageQuality: 70);
    if (image != null) {
      setState(() {
        onPicked(image);
      });
    }
  }

  void _showPickerModal(void Function(XFile?) onPicked) {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Wrap(
            children: <Widget>[
              ListTile(
                  leading: const Icon(Icons.photo_library),
                  title: const Text('Photo Library'),
                  onTap: () {
                    _pickImage(ImageSource.gallery, onPicked);
                    Navigator.of(context).pop();
                  }),
              ListTile(
                leading: const Icon(Icons.photo_camera),
                title: const Text('Camera'),
                onTap: () {
                  _pickImage(ImageSource.camera, onPicked);
                  Navigator.of(context).pop();
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _submitRegistration() async {
    setState(() => _isLoading = true);
    
    try {
      final formData = FormData.fromMap({
        'branch_code': 'PG-NRD-01',
        'full_name': _fullNameController.text,
        'phone': _phoneController.text,
        'email': _emailController.text,
        'password': 'password123',
        'aadhaar_number': _aadhaarController.text,
        'pan_number': _panController.text,
        'parent_name': _parentNameController.text,
        'parent_phone': _parentPhoneController.text,
        'current_address': _addressController.text,
      });

      if (_profilePhoto != null) formData.files.add(MapEntry('profile_photo', await MultipartFile.fromFile(_profilePhoto!.path)));
      if (_aadhaarFront != null) formData.files.add(MapEntry('aadhaar_front', await MultipartFile.fromFile(_aadhaarFront!.path)));
      if (_aadhaarBack != null) formData.files.add(MapEntry('aadhaar_back', await MultipartFile.fromFile(_aadhaarBack!.path)));
      if (_panCard != null) formData.files.add(MapEntry('pan_card', await MultipartFile.fromFile(_panCard!.path)));

      final repo = ref.read(studentRepositoryProvider);
      await repo.register(formData);

      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const RegistrationSubmittedScreen()),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Registration Failed: $e')));
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

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
              CustomCard(
                backgroundColor: AppColors.primary,
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.qr_code_2_rounded, color: AppColors.accent, size: 24),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.accent.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              'BRANCH LOCKED',
                              style: AppTypography.caption.copyWith(color: AppColors.accent, fontWeight: FontWeight.bold),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text('Naroda Branch (PG-NRD-01)', style: AppTypography.titleMedium.copyWith(color: Colors.white)),
                          Text('102, Main Highway Road, Naroda', style: AppTypography.bodySmall.copyWith(color: Colors.white70)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              Text('Personal Details', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Full Name', hint: 'Enter your legal name', prefixIcon: Icons.person_outline_rounded, controller: _fullNameController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Mobile Number', hint: '+91 XXXXX XXXXX', prefixIcon: Icons.phone_android_rounded, keyboardType: TextInputType.phone, controller: _phoneController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Email Address', hint: 'name@domain.com', prefixIcon: Icons.email_outlined, keyboardType: TextInputType.emailAddress, controller: _emailController),
              const SizedBox(height: AppSpacing.xxl),

              Text('KYC Identification', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Aadhaar Card Number', hint: '12-digit Aadhaar Number', prefixIcon: Icons.badge_outlined, keyboardType: TextInputType.number, controller: _aadhaarController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'PAN Card Number', hint: '10-character PAN', prefixIcon: Icons.credit_card_rounded, controller: _panController),
              const SizedBox(height: AppSpacing.xxl),

              Text('Parent / Guardian Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Parent / Guardian Name', hint: 'Father or Guardian Full Name', prefixIcon: Icons.family_restroom_rounded, controller: _parentNameController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Parent / Guardian Phone', hint: '+91 XXXXX XXXXX', prefixIcon: Icons.phone_callback_rounded, keyboardType: TextInputType.phone, controller: _parentPhoneController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Permanent Address', hint: 'House No, Street, City, Pincode', prefixIcon: Icons.location_on_outlined, maxLines: 2, controller: _addressController),
              const SizedBox(height: AppSpacing.xxl),

              Text('Document Attachments', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Student Profile Passport Photo',
                file: _profilePhoto,
                icon: Icons.account_box_rounded,
                onTap: () => _showPickerModal((img) => _profilePhoto = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Front)',
                file: _aadhaarFront,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal((img) => _aadhaarFront = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Back)',
                file: _aadhaarBack,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal((img) => _aadhaarBack = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'PAN Card Attachment',
                file: _panCard,
                icon: Icons.badge_rounded,
                onTap: () => _showPickerModal((img) => _panCard = img),
              ),
              const SizedBox(height: AppSpacing.xxxl),

              if (_isLoading)
                const Center(child: CircularProgressIndicator(color: AppColors.primary))
              else
                CustomButton(
                  text: 'Submit Resident Registration',
                  icon: Icons.arrow_forward_rounded,
                  onPressed: _submitRegistration,
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
    required XFile? file,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    final isUploaded = file != null;
    return CustomCard(
      onTap: onTap,
      backgroundColor: isUploaded ? AppColors.success.withOpacity(0.05) : Colors.white,
      border: Border.all(
        color: isUploaded ? AppColors.success.withOpacity(0.4) : AppColors.divider,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: (isUploaded ? AppColors.success : AppColors.secondary).withOpacity(0.12),
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
                  isUploaded ? 'File Attached: ${file.name}' : 'Tap to select document file',
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
