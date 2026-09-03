import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/network/api_client.dart';
import '../../core/widgets/custom_button.dart';
import '../login/login_screen.dart';
import '../student_registration/student_registration_screen.dart';

class QRScannerGateScreen extends ConsumerStatefulWidget {
  const QRScannerGateScreen({super.key});

  @override
  ConsumerState<QRScannerGateScreen> createState() => _QRScannerGateScreenState();
}

class _QRScannerGateScreenState extends ConsumerState<QRScannerGateScreen>
    with SingleTickerProviderStateMixin {
  late MobileScannerController _scannerController;
  late AnimationController _laserController;
  late Animation<double> _laserAnimation;

  bool _isProcessing = false;
  bool _isFlashOn = false;

  @override
  void initState() {
    super.initState();
    _scannerController = MobileScannerController(
      facing: CameraFacing.back,
      detectionSpeed: DetectionSpeed.normal,
      returnImage: false,
    );

    _laserController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat(reverse: true);

    _laserAnimation = Tween<double>(begin: 0.05, end: 0.95).animate(
      CurvedAnimation(parent: _laserController, curve: Curves.easeInOut),
    );
  }

  void _onDetect(BarcodeCapture capture) {
    if (_isProcessing) return;

    for (final barcode in capture.barcodes) {
      final String? rawValue = barcode.rawValue;
      if (rawValue != null && rawValue.trim().isNotEmpty) {
        _verifyScannedQr(rawValue.trim());
        break;
      }
    }
  }

  Future<void> _verifyScannedQr(String qrPayload) async {
    setState(() => _isProcessing = true);
    HapticFeedback.mediumImpact();

    try {
      final dio = ref.read(apiClientProvider);
      final res = await dio.post('/branch/verify-qr', data: {
        'qr_data': qrPayload,
      });

      if (res.data != null && res.data['status'] == 'success') {
        final branchData = Map<String, dynamic>.from(res.data['data']);
        if (mounted) {
          _showSuccessBottomSheet(branchData);
        }
      } else {
        throw Exception(res.data?['message'] ?? 'Invalid Branch QR');
      }
    } catch (e) {
      String msg = 'Invalid or Inactive Branch QR Code.';
      if (e is DioException && e.response?.data != null) {
        msg = e.response?.data['message']?.toString() ?? msg;
      }
      if (mounted) {
        _showErrorDialog(msg);
      }
    }
  }

  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.card,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: const [
            Icon(Icons.error_outline_rounded, color: AppColors.error, size: 24),
            SizedBox(width: 8),
            Text(
              'Invalid Branch QR',
              style: TextStyle(color: AppColors.textPrimary, fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: Text(
          message,
          style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
        ),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
            onPressed: () {
              Navigator.of(ctx).pop();
              setState(() => _isProcessing = false);
            },
            child: const Text('Scan Again', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showSuccessBottomSheet(Map<String, dynamic> branch) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isDismissible: false,
      enableDrag: false,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(AppSpacing.xl),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.success.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.check_circle_rounded, color: AppColors.success, size: 28),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Branch QR Code Verified!',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: AppColors.success,
                        ),
                      ),
                      Text(
                        'Branch Locked: ${branch['code']}',
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.lg),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(branch['name'] ?? 'Branch Name', style: AppTypography.titleMedium),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 14, color: AppColors.secondary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${branch['address'] ?? ''}, ${branch['city'] ?? ''}',
                          style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.person_outline, size: 14, color: AppColors.secondary),
                      const SizedBox(width: 4),
                      Text(
                        'Manager: ${branch['manager_name'] ?? 'Rudra PG Staff'} (${branch['manager_phone'] ?? branch['phone'] ?? 'N/A'})',
                        style: AppTypography.caption.copyWith(color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),
            CustomButton(
              text: 'Proceed to Student Registration',
              icon: Icons.how_to_reg_rounded,
              onPressed: () {
                Navigator.of(ctx).pop();
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => StudentRegistrationScreen(verifiedBranch: branch),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _scannerController.dispose();
    _laserController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B132B),
      body: SafeArea(
        child: Column(
          children: [
            // Top App Bar
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl, vertical: AppSpacing.md),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppColors.secondary.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.apartment_rounded, color: AppColors.secondary, size: 20),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        'Rudra Group PG',
                        style: AppTypography.titleMedium.copyWith(color: Colors.white, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  // Login Button for Approved Residents
                  TextButton.icon(
                    onPressed: () {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(builder: (context) => const LoginScreen()),
                      );
                    },
                    icon: const Icon(Icons.login_rounded, size: 16, color: AppColors.accent),
                    label: const Text(
                      'Login',
                      style: TextStyle(color: AppColors.accent, fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: AppSpacing.sm),

            // Title Badge & Instructions
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.secondary.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.secondary.withValues(alpha: 0.3)),
              ),
              child: const Text(
                'LIVE QR SCANNER',
                style: TextStyle(
                  color: AppColors.accent,
                  fontWeight: FontWeight.bold,
                  fontSize: 11,
                  letterSpacing: 1.2,
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.xs),
            Text(
              'Scan Branch QR Code',
              style: AppTypography.displayMedium.copyWith(color: Colors.white, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 4),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl),
              child: Text(
                'Align the reception standee QR code inside the viewfinder to begin registration.',
                style: AppTypography.bodySmall.copyWith(color: Colors.white70),
                textAlign: TextAlign.center,
              ),
            ),

            const Spacer(),

            // Real-Time Camera Viewfinder
            Center(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(28),
                child: Container(
                  width: 290,
                  height: 290,
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(28),
                    border: Border.all(color: AppColors.secondary.withValues(alpha: 0.5), width: 2),
                  ),
                  child: Stack(
                    alignment: Alignment.center,
                    children: [
                      // Live Camera Stream
                      MobileScanner(
                        controller: _scannerController,
                        onDetect: _onDetect,
                        errorBuilder: (context, error) {
                          return Center(
                            child: Padding(
                              padding: const EdgeInsets.all(AppSpacing.md),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.videocam_off_rounded, color: AppColors.error, size: 36),
                                  const SizedBox(height: 8),
                                  const Text(
                                    'Camera Access Required',
                                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    error.errorDetails?.message ?? 'Please grant camera permission in phone settings.',
                                    style: const TextStyle(color: Colors.white60, fontSize: 11),
                                    textAlign: TextAlign.center,
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),

                      // Viewfinder corner reticles
                      Positioned(top: 14, left: 14, child: _buildCorner(0)),
                      Positioned(top: 14, right: 14, child: _buildCorner(1)),
                      Positioned(bottom: 14, left: 14, child: _buildCorner(2)),
                      Positioned(bottom: 14, right: 14, child: _buildCorner(3)),

                      // Animated Scanning Laser Bar
                      if (!_isProcessing)
                        AnimatedBuilder(
                          animation: _laserAnimation,
                          builder: (context, child) {
                            return Positioned(
                              top: 20 + (_laserAnimation.value * 240),
                              left: 20,
                              right: 20,
                              child: Container(
                                height: 3,
                                decoration: BoxDecoration(
                                  color: AppColors.accent,
                                  boxShadow: [
                                    BoxShadow(
                                      color: AppColors.accent.withValues(alpha: 0.9),
                                      blurRadius: 12,
                                      spreadRadius: 2,
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),

                      // Processing Spinner Overlay
                      if (_isProcessing)
                        Container(
                          width: double.infinity,
                          height: double.infinity,
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.75),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: const [
                              CircularProgressIndicator(color: AppColors.accent),
                              SizedBox(height: 14),
                              Text(
                                'Verifying Branch...',
                                style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),

            const SizedBox(height: AppSpacing.lg),

            // Flashlight Toggle Button
            IconButton.filled(
              onPressed: () {
                if (_scannerController.value.isInitialized && _scannerController.value.isRunning) {
                  _scannerController.toggleTorch();
                  setState(() => _isFlashOn = !_isFlashOn);
                }
              },
              style: IconButton.styleFrom(
                backgroundColor: _isFlashOn ? AppColors.accent : Colors.white12,
                foregroundColor: _isFlashOn ? Colors.black : Colors.white,
                padding: const EdgeInsets.all(14),
              ),
              icon: Icon(_isFlashOn ? Icons.flash_on_rounded : Icons.flash_off_rounded, size: 22),
              tooltip: 'Toggle Flashlight',
            ),

            const Spacer(),

            // Footer Bottom Prompt
            Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.xl),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.verified_user_rounded, size: 14, color: AppColors.secondary),
                  const SizedBox(width: 6),
                  Text(
                    'Official Rudra Group PG Verification System',
                    style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCorner(int quadrant) {
    return Container(
      width: 24,
      height: 24,
      decoration: BoxDecoration(
        border: Border(
          top: (quadrant == 0 || quadrant == 1)
              ? const BorderSide(color: AppColors.accent, width: 3.5)
              : BorderSide.none,
          bottom: (quadrant == 2 || quadrant == 3)
              ? const BorderSide(color: AppColors.accent, width: 3.5)
              : BorderSide.none,
          left: (quadrant == 0 || quadrant == 2)
              ? const BorderSide(color: AppColors.accent, width: 3.5)
              : BorderSide.none,
          right: (quadrant == 1 || quadrant == 3)
              ? const BorderSide(color: AppColors.accent, width: 3.5)
              : BorderSide.none,
        ),
      ),
    );
  }
}
