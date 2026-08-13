
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:http_parser/http_parser.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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

class IndianPhoneFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    String text = newValue.text;
    if (text.startsWith('+91 ')) {
      text = text.substring(4);
    } else if (text.startsWith('+91')) {
      text = text.substring(3);
    }
    
    String digits = text.replaceAll(RegExp(r'\D'), '');
    if (digits.startsWith('91') && digits.length > 10) {
      digits = digits.substring(2);
    }
    if (digits.length > 10) {
      digits = digits.substring(0, 10);
    }

    String formatted = '';
    if (digits.isNotEmpty) {
      formatted = '+91 ';
      if (digits.length <= 5) {
        formatted += digits;
      } else {
        formatted += '${digits.substring(0, 5)} ${digits.substring(5)}';
      }
    }

    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}

class StudentRegistrationScreen extends ConsumerStatefulWidget {
  const StudentRegistrationScreen({super.key});

  @override
  ConsumerState<StudentRegistrationScreen> createState() => _StudentRegistrationScreenState();
}

class _StudentRegistrationScreenState extends ConsumerState<StudentRegistrationScreen> {
  final _fullNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _aadhaarController = TextEditingController();
  final _panController = TextEditingController();
  final _parentNameController = TextEditingController();
  final _parentPhoneController = TextEditingController();
  final _addressController = TextEditingController();

  XFile? _profilePhoto;
  XFile? _aadhaarFront;
  XFile? _aadhaarBack;
  XFile? _panCard;

  bool _isLoading = false;

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppColors.error,
          content: Row(
            children: [
              const Icon(Icons.warning_amber_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 8),
              Expanded(child: Text(message, style: const TextStyle(fontWeight: FontWeight.bold))),
            ],
          ),
        ),
      );
    }
  }

  void _showZoomableImageViewer(BuildContext context, String title, XFile file) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: const BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(title, style: AppTypography.titleSmall.copyWith(color: Colors.white)),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Colors.white),
                    onPressed: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
            ),
            Container(
              constraints: const BoxConstraints(maxHeight: 450),
              color: Colors.black,
              child: InteractiveViewer(
                minScale: 0.5,
                maxScale: 4.0,
                child: kIsWeb
                    ? Image.network(file.path, fit: BoxFit.contain)
                    : Image.file(File(file.path), fit: BoxFit.contain),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _pickImage(ImageSource source, void Function(XFile?) onPicked) async {
    if (!kIsWeb) {
      final permission = source == ImageSource.camera ? Permission.camera : Permission.photos;
      final status = await permission.request();

      if (!status.isGranted) {
        _showError('Permission required to access ${source == ImageSource.camera ? "camera" : "gallery"}');
        return;
      }
    }
    
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: source, imageQuality: 75);
    if (image != null) {
      final bytes = await image.length();
      if (bytes > 5 * 1024 * 1024) {
        _showError('Image size exceeds 5MB limit. Please select a smaller photo.');
        return;
      }
      setState(() {
        onPicked(image);
      });
    }
  }

  void _showPickerModal(String title, XFile? currentFile, void Function(XFile?) onPicked) {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Wrap(
            children: <Widget>[
              if (currentFile != null)
                ListTile(
                  leading: const Icon(Icons.zoom_in_rounded, color: AppColors.primary),
                  title: const Text('View Full Screen (Zoom)'),
                  onTap: () {
                    Navigator.of(context).pop();
                    _showZoomableImageViewer(context, title, currentFile);
                  },
                ),
              ListTile(
                  leading: const Icon(Icons.photo_library),
                  title: const Text('Choose from Photo Library'),
                  onTap: () {
                    _pickImage(ImageSource.gallery, onPicked);
                    Navigator.of(context).pop();
                  }),
              ListTile(
                leading: const Icon(Icons.photo_camera),
                title: const Text('Take Photo with Camera'),
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

  String _clean10Digits(String text) {
    String digits = text.replaceAll(RegExp(r'\D'), '');
    if (digits.length > 10 && digits.startsWith('91')) {
      digits = digits.substring(2);
    }
    if (digits.length > 10) {
      digits = digits.substring(digits.length - 10);
    }
    return digits;
  }

  Future<void> _submitRegistration() async {
    final phoneDigits = _clean10Digits(_phoneController.text);
    final parentPhoneDigits = _clean10Digits(_parentPhoneController.text);
    final aadhaarDigits = _aadhaarController.text.replaceAll(RegExp(r'\D'), '');
    final panCode = _panController.text.trim().toUpperCase();

    if (_fullNameController.text.trim().length < 3) {
      _showError('Please enter your full legal name (min 3 characters).');
      return;
    }
    if (phoneDigits.length != 10 || !RegExp(r'^[6-9]\d{9}$').hasMatch(phoneDigits)) {
      _showError('Please enter a valid 10-digit student mobile number.');
      return;
    }
    if (!_emailController.text.contains('@') || !_emailController.text.contains('.')) {
      _showError('Please enter a valid email address.');
      return;
    }
    if (aadhaarDigits.length != 12) {
      _showError('Please enter a valid 12-digit Aadhaar card number.');
      return;
    }
    if (panCode.isNotEmpty && !RegExp(r'^[A-Z]{5}[0-9]{4}[A-Z]{1}$').hasMatch(panCode)) {
      _showError('Please enter a valid 10-character PAN Card number (e.g. ABCDE1234F).');
      return;
    }
    if (_parentNameController.text.trim().length < 3) {
      _showError('Please enter parent or guardian name.');
      return;
    }
    if (parentPhoneDigits.length != 10 || !RegExp(r'^[6-9]\d{9}$').hasMatch(parentPhoneDigits)) {
      _showError('Please enter a valid 10-digit parent/guardian mobile number.');
      return;
    }
    if (_addressController.text.trim().length < 10) {
      _showError('Please enter complete permanent address (min 10 characters).');
      return;
    }

    setState(() => _isLoading = true);
    
    try {
      final formData = FormData.fromMap({
        'branch_code': 'PG-NRD-01',
        'full_name': _fullNameController.text.trim(),
        'phone': phoneDigits,
        'email': _emailController.text.trim(),
        'password': 'password123',
        'aadhaar_number': aadhaarDigits,
        'pan_number': panCode,
        'parent_name': _parentNameController.text.trim(),
        'parent_phone': parentPhoneDigits,
        'current_address': _addressController.text.trim(),
      });

      if (_profilePhoto != null) formData.files.add(MapEntry('profile_photo', MultipartFile.fromBytes(await _profilePhoto!.readAsBytes(), filename: 'profile_photo.jpg', contentType: MediaType('image', 'jpeg'))));
      if (_aadhaarFront != null) formData.files.add(MapEntry('aadhaar_front', MultipartFile.fromBytes(await _aadhaarFront!.readAsBytes(), filename: 'aadhaar_front.jpg', contentType: MediaType('image', 'jpeg'))));
      if (_aadhaarBack != null) formData.files.add(MapEntry('aadhaar_back', MultipartFile.fromBytes(await _aadhaarBack!.readAsBytes(), filename: 'aadhaar_back.jpg', contentType: MediaType('image', 'jpeg'))));
      if (_panCard != null) formData.files.add(MapEntry('pan_card', MultipartFile.fromBytes(await _panCard!.readAsBytes(), filename: 'pan_card.jpg', contentType: MediaType('image', 'jpeg'))));

      final repo = ref.read(studentRepositoryProvider);
      final appReference = await repo.register(formData);

      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => RegistrationSubmittedScreen(appReference: appReference)),
        );
      }
    } catch (e) {
      String errorMsg = e.toString();
      if (e is DioException && e.response?.data != null) {
        final data = e.response!.data;
        if (data is Map && data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          errorMsg = errors.values.first[0].toString();
        } else if (data is Map && data['message'] != null) {
          errorMsg = data['message'].toString();
        } else {
          errorMsg = e.message ?? 'Unknown error';
        }
      }
      _showError('Registration Failed: $errorMsg');
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
              CustomTextField(
                label: 'Mobile Number',
                hint: '+91 XXXXX XXXXX',
                prefixIcon: Icons.phone_android_rounded,
                keyboardType: TextInputType.phone,
                controller: _phoneController,
                inputFormatters: [IndianPhoneFormatter()],
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Email Address', hint: 'name@domain.com', prefixIcon: Icons.email_outlined, keyboardType: TextInputType.emailAddress, controller: _emailController),
              const SizedBox(height: AppSpacing.xxl),

              Text('KYC Identification', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Aadhaar Card Number', hint: '12-digit Aadhaar Number', prefixIcon: Icons.badge_outlined, keyboardType: TextInputType.number, controller: _aadhaarController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'PAN Card Number',
                hint: '10-character PAN (e.g. ABCDE1234F)',
                prefixIcon: Icons.credit_card_rounded,
                controller: _panController,
                textCapitalization: TextCapitalization.characters,
              ),
              const SizedBox(height: AppSpacing.xxl),

              Text('Parent / Guardian Contact', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Parent / Guardian Name', hint: 'Father or Guardian Full Name', prefixIcon: Icons.family_restroom_rounded, controller: _parentNameController),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Parent / Guardian Phone',
                hint: '+91 XXXXX XXXXX',
                prefixIcon: Icons.phone_callback_rounded,
                keyboardType: TextInputType.phone,
                controller: _parentPhoneController,
                inputFormatters: [IndianPhoneFormatter()],
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(label: 'Permanent Address', hint: 'House No, Street, City, Pincode', prefixIcon: Icons.location_on_outlined, maxLines: 2, controller: _addressController),
              const SizedBox(height: AppSpacing.xxl),

              Text('Document Attachments', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Student Profile Passport Photo',
                file: _profilePhoto,
                icon: Icons.account_box_rounded,
                onTap: () => _showPickerModal('Profile Photo', _profilePhoto, (img) => _profilePhoto = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Front)',
                file: _aadhaarFront,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal('Aadhaar Front', _aadhaarFront, (img) => _aadhaarFront = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Back)',
                file: _aadhaarBack,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal('Aadhaar Back', _aadhaarBack, (img) => _aadhaarBack = img),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'PAN Card Attachment',
                file: _panCard,
                icon: Icons.badge_rounded,
                onTap: () => _showPickerModal('PAN Card', _panCard, (img) => _panCard = img),
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
                  isUploaded ? 'Attached: ${file.name} (Tap to Zoom)' : 'Tap to select document file',
                  style: AppTypography.bodySmall.copyWith(
                    color: isUploaded ? AppColors.success : AppColors.textSecondary,
                    fontWeight: isUploaded ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ],
            ),
          ),
          if (isUploaded)
            IconButton(
              icon: const Icon(Icons.zoom_in_rounded, color: AppColors.primary),
              onPressed: () => _showZoomableImageViewer(context, title, file),
            )
          else
            const Icon(
              Icons.cloud_upload_outlined,
              color: AppColors.textSecondary,
              size: 20,
            ),
        ],
      ),
    );
  }
}
