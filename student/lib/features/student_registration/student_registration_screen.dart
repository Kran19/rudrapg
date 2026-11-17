import 'dart:io';
import 'dart:typed_data';
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

class StudentRegistrationScreen extends ConsumerStatefulWidget {
  final Map<String, dynamic>? verifiedBranch;
  const StudentRegistrationScreen({super.key, this.verifiedBranch});

  @override
  ConsumerState<StudentRegistrationScreen> createState() =>
      _StudentRegistrationScreenState();
}

class _StudentRegistrationScreenState
    extends ConsumerState<StudentRegistrationScreen> {
  final _fullNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _aadhaarController = TextEditingController();
  final _panController = TextEditingController();
  final _parentNameController = TextEditingController();
  final _parentPhoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _meterReadingController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  XFile? _profilePhoto;
  Uint8List? _profilePhotoBytes;

  XFile? _aadhaarFront;
  Uint8List? _aadhaarFrontBytes;

  XFile? _aadhaarBack;
  Uint8List? _aadhaarBackBytes;

  XFile? _panCard;
  Uint8List? _panCardBytes;

  XFile? _meterPhoto;
  Uint8List? _meterPhotoBytes;

  bool _isLoading = false;

  @override
  void dispose() {
    _fullNameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _aadhaarController.dispose();
    _panController.dispose();
    _parentNameController.dispose();
    _parentPhoneController.dispose();
    _addressController.dispose();
    _meterReadingController.dispose();
    super.dispose();
  }

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppColors.error,
          content: Row(
            children: [
              const Icon(
                Icons.warning_amber_rounded,
                color: Colors.white,
                size: 20,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  message,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      );
    }
  }

  void _showZoomableImageViewer(
    BuildContext context,
    String title,
    XFile file,
    Uint8List? bytes,
  ) {
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
                  Expanded(
                    child: Text(
                      title,
                      style: AppTypography.titleSmall.copyWith(
                        color: Colors.white,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
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
                child: bytes != null
                    ? Image.memory(bytes, fit: BoxFit.contain)
                    : (kIsWeb
                        ? Image.network(file.path, fit: BoxFit.contain)
                        : Image.file(File(file.path), fit: BoxFit.contain)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _pickImage(
    ImageSource source,
    void Function(XFile?, Uint8List?) onPicked,
  ) async {
    if (!kIsWeb) {
      if (source == ImageSource.camera) {
        final status = await Permission.camera.request();
        if (!status.isGranted) {
          _showError('Permission required to access camera');
          return;
        }
      }
    }

    try {
      final ImagePicker picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: source,
        imageQuality: 70,
        maxWidth: 1200,
        maxHeight: 1200,
      );
      if (image != null) {
        // Read raw bytes immediately into memory to prevent blob revocation in Web
        final Uint8List bytes = await image.readAsBytes();
        if (bytes.length > 10 * 1024 * 1024) {
          _showError(
            'Image size exceeds 10MB limit. Please select a smaller photo.',
          );
          return;
        }
        setState(() {
          onPicked(image, bytes);
        });
      }
    } catch (e) {
      _showError('Error reading selected image: $e');
    }
  }

  void _showPickerModal(
    String title,
    XFile? currentFile,
    Uint8List? currentBytes,
    void Function(XFile?, Uint8List?) onPicked,
  ) {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Wrap(
            children: <Widget>[
              if (currentFile != null || currentBytes != null)
                ListTile(
                  leading: const Icon(
                    Icons.zoom_in_rounded,
                    color: AppColors.primary,
                  ),
                  title: const Text('View Full Screen (Zoom)'),
                  onTap: () {
                    Navigator.of(context).pop();
                    if (currentFile != null) {
                      _showZoomableImageViewer(
                        context,
                        title,
                        currentFile,
                        currentBytes,
                      );
                    }
                  },
                ),
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Choose from Photo Library'),
                onTap: () {
                  _pickImage(ImageSource.gallery, onPicked);
                  Navigator.of(context).pop();
                },
              ),
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

  Future<void> _submitRegistration() async {
    final phoneDigits = _phoneController.text.trim();
    final parentPhoneDigits = _parentPhoneController.text.trim();
    final aadhaarDigits = _aadhaarController.text.replaceAll(RegExp(r'\D'), '');
    final panCode = _panController.text.trim().toUpperCase();

    if (_fullNameController.text.trim().length < 3) {
      _showError('Please enter your full legal name (min 3 characters).');
      return;
    }
    if (phoneDigits.length != 10 ||
        !RegExp(r'^\d{10}$').hasMatch(phoneDigits)) {
      _showError('Please enter a valid 10-digit student mobile number.');
      return;
    }
    if (!_emailController.text.contains('@') ||
        !_emailController.text.contains('.')) {
      _showError('Please enter a valid email address.');
      return;
    }
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;
    if (password.length < 6) {
      _showError('Please enter a password with at least 6 characters.');
      return;
    }
    if (password != confirmPassword) {
      _showError('Password and Confirm Password do not match.');
      return;
    }
    if (aadhaarDigits.length != 12) {
      _showError('Please enter a valid 12-digit Aadhaar card number.');
      return;
    }
    if (panCode.isNotEmpty &&
        !RegExp(r'^[A-Z]{5}[0-9]{4}[A-Z]{1}$').hasMatch(panCode)) {
      _showError(
        'Please enter a valid 10-character PAN Card number (e.g. ABCDE1234F).',
      );
      return;
    }
    if (_parentNameController.text.trim().length < 3) {
      _showError('Please enter parent or guardian name.');
      return;
    }
    if (parentPhoneDigits.length != 10 ||
        !RegExp(r'^\d{10}$').hasMatch(parentPhoneDigits)) {
      _showError(
        'Please enter a valid 10-digit parent/guardian mobile number.',
      );
      return;
    }
    if (_addressController.text.trim().length < 10) {
      _showError(
        'Please enter complete permanent address (min 10 characters).',
      );
      return;
    }

    if (_profilePhotoBytes == null) {
      _showError('Please upload your Student Profile Passport Photo.');
      return;
    }
    if (_aadhaarFrontBytes == null) {
      _showError('Please upload your Aadhaar Card (Front) image.');
      return;
    }
    if (_aadhaarBackBytes == null) {
      _showError('Please upload your Aadhaar Card (Back) image.');
      return;
    }
    if (_panCardBytes == null) {
      _showError('Please upload your PAN Card Attachment.');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final branchCode =
          widget.verifiedBranch?['code']?.toString() ?? 'PG-NRD-01';
      final formData = FormData.fromMap({
        'branch_code': branchCode,
        'full_name': _fullNameController.text.trim(),
        'phone': phoneDigits,
        'email': _emailController.text.trim(),
        'password': password,
        'aadhaar_number': aadhaarDigits,
        'pan_number': panCode,
        'parent_name': _parentNameController.text.trim(),
        'parent_phone': parentPhoneDigits,
        'current_address': _addressController.text.trim(),
        'starting_meter_reading': _meterReadingController.text.trim(),
      });

      if (_profilePhotoBytes != null) {
        formData.files.add(
          MapEntry(
            'profile_photo',
            MultipartFile.fromBytes(
              _profilePhotoBytes!,
              filename: 'profile_photo.jpg',
              contentType: MediaType('image', 'jpeg'),
            ),
          ),
        );
      }
      if (_aadhaarFrontBytes != null) {
        formData.files.add(
          MapEntry(
            'aadhaar_front',
            MultipartFile.fromBytes(
              _aadhaarFrontBytes!,
              filename: 'aadhaar_front.jpg',
              contentType: MediaType('image', 'jpeg'),
            ),
          ),
        );
      }
      if (_aadhaarBackBytes != null) {
        formData.files.add(
          MapEntry(
            'aadhaar_back',
            MultipartFile.fromBytes(
              _aadhaarBackBytes!,
              filename: 'aadhaar_back.jpg',
              contentType: MediaType('image', 'jpeg'),
            ),
          ),
        );
      }
      if (_panCardBytes != null) {
        formData.files.add(
          MapEntry(
            'pan_card',
            MultipartFile.fromBytes(
              _panCardBytes!,
              filename: 'pan_card.jpg',
              contentType: MediaType('image', 'jpeg'),
            ),
          ),
        );
      }
      if (_meterPhotoBytes != null) {
        formData.files.add(
          MapEntry(
            'meter_photo',
            MultipartFile.fromBytes(
              _meterPhotoBytes!,
              filename: 'meter_photo.jpg',
              contentType: MediaType('image', 'jpeg'),
            ),
          ),
        );
      }

      final repo = ref.read(studentRepositoryProvider);
      final appReference = await repo.register(formData);

      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (context) =>
                RegistrationSubmittedScreen(appReference: appReference),
          ),
        );
      }
    } catch (e) {
      String errorMsg = e.toString();
      if (e is DioException) {
        if (e.response?.statusCode == 413) {
          errorMsg =
              'Uploaded files are too large. Please choose compressed or smaller photos.';
        } else if (e.response?.data != null) {
          final data = e.response!.data;
          if (data is Map && data['errors'] != null) {
            final errors = data['errors'] as Map<String, dynamic>;
            errorMsg = errors.values.first[0].toString();
          } else if (data is Map && data['message'] != null) {
            errorMsg = data['message'].toString();
          } else {
            errorMsg = e.message ?? 'Server response error';
          }
        } else {
          errorMsg = e.message ?? 'Connection error';
        }
      }
      _showError('Registration Failed: $errorMsg');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bName =
        widget.verifiedBranch?['name']?.toString() ?? 'Naroda Main Branch';
    final bCode = widget.verifiedBranch?['code']?.toString() ?? 'PG-NRD-01';
    final bAddress =
        widget.verifiedBranch?['address']?.toString() ??
        '102, Main Highway Road, Naroda';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Resident Registration'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: AppColors.primary,
      ),
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: AppSpacing.maxContentWidth),
            child: SingleChildScrollView(
              padding: AppSpacing.responsivePagePadding(context),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CustomCard(
                    backgroundColor: AppColors.primary,
                    padding: AppSpacing.responsiveCardPadding(context),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withValues(alpha: 0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.qr_code_2_rounded,
                        color: AppColors.accent,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.accent.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              'VERIFIED BRANCH QR LOCKED',
                              style: AppTypography.caption.copyWith(
                                color: AppColors.accent,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '$bName ($bCode)',
                            style: AppTypography.titleMedium.copyWith(
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            bAddress,
                            style: AppTypography.bodySmall.copyWith(
                              color: Colors.white70,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

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
                hint: 'Enter 10-digit mobile number',
                prefixIcon: Icons.phone_android_rounded,
                keyboardType: TextInputType.phone,
                controller: _phoneController,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(10),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Email Address',
                hint: 'name@domain.com',
                prefixIcon: Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
                controller: _emailController,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Create Account Password',
                hint: 'Minimum 6 characters',
                prefixIcon: Icons.lock_outline_rounded,
                obscureText: _obscurePassword,
                controller: _passwordController,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: AppColors.textSecondary,
                    size: 20,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscurePassword = !_obscurePassword;
                    });
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Confirm Password',
                hint: 'Re-enter your password',
                prefixIcon: Icons.lock_reset_rounded,
                obscureText: _obscureConfirmPassword,
                controller: _confirmPasswordController,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureConfirmPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: AppColors.textSecondary,
                    size: 20,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscureConfirmPassword = !_obscureConfirmPassword;
                    });
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              Text('KYC Identification', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'Aadhaar Card Number',
                hint: '12-digit Aadhaar Number',
                prefixIcon: Icons.badge_outlined,
                keyboardType: TextInputType.number,
                controller: _aadhaarController,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(12),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                label: 'PAN Card Number',
                hint: '10-character PAN (e.g. ABCDE1234F)',
                prefixIcon: Icons.credit_card_rounded,
                controller: _panController,
                textCapitalization: TextCapitalization.characters,
              ),
              const SizedBox(height: AppSpacing.xxl),

              Text(
                'Parent / Guardian Contact',
                style: AppTypography.titleLarge,
              ),
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
                hint: 'Enter 10-digit mobile number',
                prefixIcon: Icons.phone_callback_rounded,
                keyboardType: TextInputType.phone,
                controller: _parentPhoneController,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(10),
                ],
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

              Text('Document Attachments', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Student Profile Passport Photo',
                file: _profilePhoto,
                bytes: _profilePhotoBytes,
                icon: Icons.account_box_rounded,
                onTap: () => _showPickerModal(
                  'Profile Photo',
                  _profilePhoto,
                  _profilePhotoBytes,
                  (img, bytes) {
                    _profilePhoto = img;
                    _profilePhotoBytes = bytes;
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Front)',
                file: _aadhaarFront,
                bytes: _aadhaarFrontBytes,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal(
                  'Aadhaar Front',
                  _aadhaarFront,
                  _aadhaarFrontBytes,
                  (img, bytes) {
                    _aadhaarFront = img;
                    _aadhaarFrontBytes = bytes;
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'Aadhaar Card (Back)',
                file: _aadhaarBack,
                bytes: _aadhaarBackBytes,
                icon: Icons.file_present_rounded,
                onTap: () => _showPickerModal(
                  'Aadhaar Back',
                  _aadhaarBack,
                  _aadhaarBackBytes,
                  (img, bytes) {
                    _aadhaarBack = img;
                    _aadhaarBackBytes = bytes;
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              _buildUploadCard(
                title: 'PAN Card Attachment',
                file: _panCard,
                bytes: _panCardBytes,
                icon: Icons.badge_rounded,
                onTap: () => _showPickerModal(
                  'PAN Card',
                  _panCard,
                  _panCardBytes,
                  (img, bytes) {
                    _panCard = img;
                    _panCardBytes = bytes;
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Move-in Electricity Sub-Meter Section
              const SectionHeader(
                title: 'Move-in Electricity Sub-Meter',
              ),
              const SizedBox(height: 4),
              Text(
                'Upload your move-in sub-meter reading & photo to calculate monthly consumption bills accurately.',
                style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary),
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                label: 'Starting Sub-Meter Reading (kWh)',
                hint: 'Enter starting meter reading (e.g. 1024.50)',
                prefixIcon: Icons.electric_meter_rounded,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                controller: _meterReadingController,
              ),
              const SizedBox(height: AppSpacing.md),

              _buildUploadCard(
                title: 'Starting Sub-Meter Photo',
                file: _meterPhoto,
                bytes: _meterPhotoBytes,
                icon: Icons.camera_alt_outlined,
                onTap: () => _showPickerModal(
                  'Starting Meter Photo',
                  _meterPhoto,
                  _meterPhotoBytes,
                  (img, bytes) {
                    _meterPhoto = img;
                    _meterPhotoBytes = bytes;
                  },
                ),
              ),
              const SizedBox(height: AppSpacing.xxxl),

              if (_isLoading)
                const Center(
                  child: CircularProgressIndicator(color: AppColors.primary),
                )
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
    ),
  ),
);
}

  Widget _buildUploadCard({
    required String title,
    required XFile? file,
    required Uint8List? bytes,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    final isUploaded = file != null || bytes != null;
    return CustomCard(
      onTap: onTap,
      backgroundColor: isUploaded
          ? AppColors.success.withValues(alpha: 0.05)
          : Colors.white,
      border: Border.all(
        color: isUploaded
            ? AppColors.success.withValues(alpha: 0.4)
            : AppColors.divider,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: (isUploaded ? AppColors.success : AppColors.secondary)
                  .withValues(alpha: 0.12),
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
                Text(
                  title,
                  style: AppTypography.titleSmall,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  isUploaded
                      ? 'Attached: ${file?.name ?? 'Document'} (Tap to Zoom)'
                      : 'Tap to select document file',
                  style: AppTypography.bodySmall.copyWith(
                    color: isUploaded
                        ? AppColors.success
                        : AppColors.textSecondary,
                    fontWeight: isUploaded
                        ? FontWeight.w600
                        : FontWeight.normal,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          if (isUploaded && file != null)
            IconButton(
              icon: const Icon(Icons.zoom_in_rounded, color: AppColors.primary),
              onPressed: () => _showZoomableImageViewer(context, title, file, bytes),
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
