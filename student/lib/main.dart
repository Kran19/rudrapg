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

  Widget initialScreen = const SplashScreen();
  final screenParam = uri.queryParameters['screen'];
  if (screenParam != null) {
    switch (screenParam) {
      case 'login':
        initialScreen = const LoginScreen();
        break;
      case 'dashboard':
      case 'home':
        initialScreen = const MainLayoutScreen(initialIndex: 0);
        break;
      case 'payments':
        initialScreen = const MainLayoutScreen(initialIndex: 1);
        break;
      case 'notifications':
        initialScreen = const MainLayoutScreen(initialIndex: 2);
        break;
      case 'profile':
        initialScreen = const MainLayoutScreen(initialIndex: 3);
        break;
      case 'room':
      case 'my_room':
        initialScreen = const MyRoomScreen();
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
