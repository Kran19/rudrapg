import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../qr_scanner/qr_scanner_gate_screen.dart';
import '../main_layout/main_layout_screen.dart';
import '../auth/presentation/auth_notifier.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _mobileController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _mobileController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() {
    final phone = _mobileController.text.trim();
    final password = _passwordController.text.trim();

    if (phone.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter phone number and password'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    ref.read(authNotifierProvider.notifier).login(phone, password).then((_) {
      if (!mounted) return;
      final state = ref.read(authNotifierProvider);
      if (state.status == AuthStatus.authenticated) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const MainLayoutScreen()),
        );
      } else if (state.errorMessage != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.errorMessage!),
            backgroundColor: AppColors.error,
          ),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final isLoading = authState.status == AuthStatus.loading;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: AppSpacing.xxl),
              // Brand Logo Icon
              Center(
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.apartment_rounded,
                    size: 48,
                    color: AppColors.primary,
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // Title
              Center(
                child: Text(
                  'Resident Portal Login',
                  style: AppTypography.displayMedium,
                ),
              ),
              const SizedBox(height: AppSpacing.xs),
              Center(
                child: Text(
                  'Enter your credentials to access your room, payments & electricity meter audits.',
                  textAlign: TextAlign.center,
                  style: AppTypography.bodySmall,
                ),
              ),
              const SizedBox(height: AppSpacing.xxxl),

              // Mobile Input
              CustomTextField(
                label: 'Registered Mobile Number',
                hint: 'e.g. 9876543210',
                controller: _mobileController,
                keyboardType: TextInputType.phone,
                prefixIcon: Icons.phone_android_rounded,
              ),
              const SizedBox(height: AppSpacing.lg),

              // Password Input
              CustomTextField(
                label: 'Password',
                hint: '••••••••',
                controller: _passwordController,
                obscureText: _obscurePassword,
                prefixIcon: Icons.lock_outline_rounded,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                    color: AppColors.textSecondary,
                  ),
                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                ),
              ),
              const SizedBox(height: AppSpacing.sm),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Default Password: password123',
                    style: AppTypography.caption.copyWith(color: AppColors.textSecondary, fontStyle: FontStyle.italic),
                  ),
                  TextButton(
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Default registration password is: password123'),
                        ),
                      );
                    },
                    child: Text('Forgot Password?', style: AppTypography.caption.copyWith(color: AppColors.secondary)),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),

              // Login Action Button
              CustomButton(
                text: isLoading ? 'Signing In...' : 'Sign In to Portal',
                icon: isLoading ? Icons.hourglass_empty : Icons.login_rounded,
                isLoading: isLoading,
                onPressed: isLoading ? () {} : _handleLogin,
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Register via Branch QR Scan Card
              CustomCard(
                onTap: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const QRScannerGateScreen()),
                  );
                },
                backgroundColor: AppColors.secondary.withValues(alpha: 0.05),
                border: Border.all(color: AppColors.secondary.withValues(alpha: 0.3)),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.secondary.withValues(alpha: 0.12),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.qr_code_scanner_rounded, color: AppColors.secondary, size: 22),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('New Resident Registration?', style: AppTypography.titleSmall),
                          const SizedBox(height: 2),
                          Text('Scan branch QR code & submit details', style: AppTypography.bodySmall),
                        ],
                      ),
                    ),
                    const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.secondary),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
