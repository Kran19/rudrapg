import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'core/theme/app_theme.dart';
import 'core/network/api_client.dart';
import 'features/splash/splash_screen.dart';
import 'features/login/login_screen.dart';
import 'features/main_layout/main_layout_screen.dart';
import 'features/resident/my_room_screen.dart';
import 'features/support/support_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  final prefs = await SharedPreferences.getInstance();

  // Support direct screen preview and authenticated state via URL
  final uri = Uri.base;
  final tokenParam = uri.queryParameters['token'];
  if (tokenParam != null && tokenParam.isNotEmpty) {
    await prefs.setString('auth_token', tokenParam);
  }

  final token = prefs.getString('auth_token');
  final bool hasToken = token != null && token.isNotEmpty;

  Widget initialScreen = const SplashScreen();
  final screenParam = uri.queryParameters['screen'];
  if (screenParam != null) {
    switch (screenParam) {
      case 'login':
        initialScreen = const LoginScreen();
        break;
      case 'dashboard':
      case 'home':
        initialScreen = hasToken ? const MainLayoutScreen(initialIndex: 0) : const LoginScreen();
        break;
      case 'payments':
        initialScreen = hasToken ? const MainLayoutScreen(initialIndex: 1) : const LoginScreen();
        break;
      case 'notifications':
        initialScreen = hasToken ? const MainLayoutScreen(initialIndex: 2) : const LoginScreen();
        break;
      case 'profile':
        initialScreen = hasToken ? const MainLayoutScreen(initialIndex: 3) : const LoginScreen();
        break;
      case 'room':
      case 'my_room':
        initialScreen = hasToken ? const MyRoomScreen() : const LoginScreen();
        break;
      case 'support':
        initialScreen = const SupportScreen();
        break;
    }
  }
  
  runApp(
    ProviderScope(
      overrides: [
        sharedPreferencesProvider.overrideWithValue(prefs),
      ],
      child: RudraPGApp(initialScreen: initialScreen),
    ),
  );
}

class RudraPGApp extends StatelessWidget {
  final Widget initialScreen;
  const RudraPGApp({super.key, this.initialScreen = const SplashScreen()});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Rudra Group PG',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: initialScreen,
    );
  }
}
