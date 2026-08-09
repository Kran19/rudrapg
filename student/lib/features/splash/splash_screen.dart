import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../welcome/welcome_screen.dart';
import '../main_layout/main_layout_screen.dart';
import '../auth/presentation/auth_notifier.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/network/api_client.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeIn),
    );

    _scaleAnimation = Tween<double>(begin: 0.85, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutBack),
    );

    _controller.forward();

    // Auto navigate and check version after animations
    _checkVersionAndNavigate();
  }

  Future<void> _checkVersionAndNavigate() async {
    // Give animation some time
    await Future.delayed(const Duration(milliseconds: 2000));
    if (!mounted) return;

    try {
      final dio = ref.read(apiClientProvider);
      final response = await dio.get('/app-version');
      final data = response.data;
      
      final serverVersionStr = data['version'] as String;
      final downloadUrl = data['download_url'] as String;
      final forceUpdate = data['force_update'] as bool;

      // Get local version
      final packageInfo = await PackageInfo.fromPlatform();
      final localVersionStr = packageInfo.version;

      if (_isUpdateRequired(localVersionStr, serverVersionStr)) {
        if (!mounted) return;
        _showUpdateDialog(downloadUrl, forceUpdate);
        return; // Stop navigation
      }
    } catch (e) {
      debugPrint('Version check failed: $e');
      // On failure, just continue to app
    }

    _navigateToNextScreen();
  }

  bool _isUpdateRequired(String local, String server) {
    // Simple semver compare (e.g. 1.0.0 vs 1.0.1)
    final lParts = local.split('.').map((e) => int.tryParse(e) ?? 0).toList();
    final sParts = server.split('.').map((e) => int.tryParse(e) ?? 0).toList();
    
    for (int i = 0; i < 3; i++) {
      final l = i < lParts.length ? lParts[i] : 0;
      final s = i < sParts.length ? sParts[i] : 0;
      if (s > l) return true;
      if (l > s) return false;
    }
    return false;
  }

  void _showUpdateDialog(String downloadUrl, bool forceUpdate) {
    showDialog(
      context: context,
      barrierDismissible: !forceUpdate,
      builder: (context) {
        return PopScope(
          canPop: !forceUpdate,
          child: AlertDialog(
            backgroundColor: AppColors.card,
            title: const Text('Update Required', style: TextStyle(color: AppColors.textPrimary)),
            content: const Text(
              'A new version of the app is available. Please update to continue using the application seamlessly.',
              style: TextStyle(color: AppColors.textSecondary),
            ),
            actions: [
              if (!forceUpdate)
                TextButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                    _navigateToNextScreen();
                  },
                  child: const Text('Later', style: TextStyle(color: AppColors.textSecondary)),
                ),
              ElevatedButton(
                onPressed: () async {
                  final uri = Uri.parse(downloadUrl);
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                },
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                child: const Text('Update Now', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        );
      }
    );
  }

  void _navigateToNextScreen() {
    if (!mounted) return;
    final authState = ref.read(authNotifierProvider);
    final Widget nextScreen = authState.status == AuthStatus.authenticated
        ? const MainLayoutScreen()
        : const WelcomeScreen();

    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (context, animation, secondaryAnimation) => nextScreen,
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          return FadeTransition(opacity: animation, child: child);
        },
        transitionDuration: const Duration(milliseconds: 400),
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary,
      body: Center(
        child: AnimatedBuilder(
          animation: _controller,
          builder: (context, child) {
            return FadeTransition(
              opacity: _fadeAnimation,
              child: ScaleTransition(
                scale: _scaleAnimation,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.accent, width: 2),
                      ),
                      child: const Center(
                        child: Icon(
                          Icons.apartment_rounded,
                          size: 52,
                          color: AppColors.accent,
                        ),
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xxl),
                    Text(
                      'RUDRA GROUP PG',
                      style: AppTypography.displayMedium.copyWith(
                        color: Colors.white,
                        letterSpacing: 1.5,
                      ),
                    ),
                    const SizedBox(height: AppSpacing.sm),
                    Text(
                      'PREMIUM LIVING EXPERIENCE',
                      style: AppTypography.caption.copyWith(
                        color: AppColors.accent,
                        letterSpacing: 2.0,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
