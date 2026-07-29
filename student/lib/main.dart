import 'package:flutter/material.dart';
import 'core/theme/app_theme.dart';
import 'features/splash/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const RudraPGApp());
}

class RudraPGApp extends StatelessWidget {
  const RudraPGApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Rudra Group PG',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: const SplashScreen(),
    );
  }
}
