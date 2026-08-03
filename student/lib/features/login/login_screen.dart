import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../core/widgets/custom_text_field.dart';
import '../main_layout/main_layout_screen.dart';
import '../welcome/welcome_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _mobileController = TextEditingController(text: '9876543210');
  final _passwordController = TextEditingController(text: '••••••••');
  bool _obscurePassword = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: AppSpacing.xxl),
              // App Branding Logo Container
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppColors.primary,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: AppSpacing.softShadow,
                  ),
                  child: const Center(
                    child: Icon(
                      Icons.apartment_rounded,
                      size: 42,
                      color: AppColors.accent,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
              Center(
                child: Column(
                  children: [
                    Text('RUDRA GROUP PG', style: AppTypography.displayMedium),
                    const SizedBox(height: 4),
                    Text(
                      'RESIDENT COMPANION PORTAL',
                      style: AppTypography.caption.copyWith(
                        color: AppColors.secondary,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.xxxl),

              // Login Form Card
              Text('Resident Login', style: AppTypography.titleLarge),
              const SizedBox(height: AppSpacing.xs),
              Text(
                'Enter your registered mobile number & password to access your resident dashboard.',
                style: AppTypography.bodyMedium.copyWith(color: AppColors.textSecondary),
              ),
              const SizedBox(height: AppSpacing.xl),

              CustomTextField(
                label: 'Mobile Number',
                hint: 'Enter 10-digit mobile number',
                prefixIcon: Icons.phone_android_rounded,
                keyboardType: TextInputType.phone,
                controller: _mobileController,
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                label: 'Password',
                hint: 'Enter password',
                prefixIcon: Icons.lock_outline_rounded,
                obscureText: _obscurePassword,
                controller: _passwordController,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: AppColors.textSecondary,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscurePassword = !_obscurePassword;
                    });
                  },
                ),
              ),

              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Password reset instructions sent to your registered mobile number.'),
                      ),
                    );
                  },
                  child: Text('Forgot Password?', style: AppTypography.caption.copyWith(color: AppColors.secondary)),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // Login Action Button
              CustomButton(
                text: 'Log In to Resident Portal',
                icon: Icons.login_rounded,
                onPressed: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const MainLayoutScreen()),
                  );
                },
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Register via Branch QR Scan Card
              CustomCard(
                onTap: () {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (context) => const WelcomeScreen()),
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
