import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../core/widgets/section_header.dart';
import '../../data/models/bed_model.dart';
import '../../data/models/room_model.dart';
import 'booking_submitted_screen.dart';

class StudentRegistrationScreen extends StatefulWidget {
  final RoomModel room;
  final BedModel selectedBed;

  const StudentRegistrationScreen({
    super.key,
    required this.room,
    required this.selectedBed,
  });

  @override
  State<StudentRegistrationScreen> createState() => _StudentRegistrationScreenState();
}

class _StudentRegistrationScreenState extends State<StudentRegistrationScreen> {
  bool _aadhaarFrontUploaded = true;
  bool _aadhaarBackUploaded = true;
  bool _panUploaded = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const CustomAppBar(title: 'Student KYC Registration'),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Selected Booking Summary Card
            CustomCard(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('SELECTED ACCOMMODATION', style: AppTypography.badge.copyWith(color: AppColors.accent)),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Room ${widget.room.roomNumber} (${widget.selectedBed.code})', style: AppTypography.titleLarge.copyWith(color: Colors.white)),
                      Text('₹${widget.room.monthlyRent.toInt()}/mo', style: AppTypography.titleLarge.copyWith(color: AppColors.accent)),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('${widget.room.sharingType} • Security Deposit: ₹${widget.room.securityDeposit.toInt()}', style: AppTypography.bodySmall.copyWith(color: Colors.white70)),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Personal Information Section
            SectionHeader(title: 'Personal Details'),
            const CustomTextField(
              label: 'Full Name (As per Aadhaar)',
              hint: 'e.g. Rahul Mahesh Sharma',
              initialValue: 'Rahul Sharma',
              prefixIcon: Icons.person_outline_rounded,
            ),
            const SizedBox(height: AppSpacing.lg),
            const CustomTextField(
              label: 'Mobile Phone Number',
              hint: 'e.g. 9876543210',
              initialValue: '9876543210',
              keyboardType: TextInputType.phone,
              prefixIcon: Icons.phone_android_rounded,
            ),
            const SizedBox(height: AppSpacing.lg),
            const CustomTextField(
              label: 'Aadhaar Number',
              hint: '12 Digit Aadhaar Number',
              initialValue: '9988-7766-5544',
              keyboardType: TextInputType.number,
              prefixIcon: Icons.badge_outlined,
            ),
            const SizedBox(height: AppSpacing.lg),
            const CustomTextField(
              label: 'PAN Number (Optional)',
              hint: '10 Character PAN Number',
              initialValue: 'ABCDE1234F',
              prefixIcon: Icons.credit_card_rounded,
            ),
            const SizedBox(height: AppSpacing.xxl),

            // KYC Documents Upload UI
            SectionHeader(title: 'KYC Document Verification'),
            _buildFileUploadTile('Aadhaar Card Front', _aadhaarFrontUploaded, () {
              setState(() => _aadhaarFrontUploaded = !_aadhaarFrontUploaded);
            }),
            const SizedBox(height: AppSpacing.md),
            _buildFileUploadTile('Aadhaar Card Back', _aadhaarBackUploaded, () {
              setState(() => _aadhaarBackUploaded = !_aadhaarBackUploaded);
            }),
            const SizedBox(height: AppSpacing.md),
            _buildFileUploadTile('PAN Card Photo', _panUploaded, () {
              setState(() => _panUploaded = !_panUploaded);
            }),
            const SizedBox(height: AppSpacing.xxxl),

            // Submit Booking Request Button
            CustomButton(
              text: 'Submit Booking Application',
              type: CustomButtonType.secondary,
              onPressed: () {
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(
                    builder: (_) => BookingSubmittedScreen(
                      room: widget.room,
                      selectedBed: widget.selectedBed,
                    ),
                  ),
                );
              },
            ),
            const SizedBox(height: AppSpacing.lg),
          ],
        ),
      ),
    );
  }

  Widget _buildFileUploadTile(String title, bool isUploaded, VoidCallback onTap) {
    return CustomCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
      child: Row(
        children: [
          Icon(
            isUploaded ? Icons.check_circle_rounded : Icons.cloud_upload_outlined,
            color: isUploaded ? AppColors.success : AppColors.secondary,
            size: 28,
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: AppTypography.titleSmall),
                Text(isUploaded ? 'File Attached (aadhaar_doc.jpg)' : 'Tap to upload document', style: AppTypography.bodySmall),
              ],
            ),
          ),
          Text(
            isUploaded ? 'Change' : 'Upload',
            style: AppTypography.bodySmall.copyWith(
              color: AppColors.secondary,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }
}
