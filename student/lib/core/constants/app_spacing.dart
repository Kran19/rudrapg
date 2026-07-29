import 'package:flutter/material.dart';

/// Global Spacing & Layout Tokens
class AppSpacing {
  AppSpacing._();

  static const double xs = 4.0;
  static const double sm = 8.0;
  static const double md = 12.0;
  static const double lg = 16.0;
  static const double xl = 20.0;
  static const double xxl = 24.0;
  static const double xxxl = 32.0;
  static const double section = 40.0;
  static const double hero = 48.0;

  // Radius Tokens
  static const double radiusButton = 16.0;
  static const double radiusCard = 20.0;
  static const double radiusBottomSheet = 28.0;
  static const double radiusInput = 14.0;
  static const double radiusBadge = 8.0;

  // Height Tokens
  static const double buttonHeight = 56.0;
  static const double inputHeight = 56.0;

  // EdgeInsets Helpers
  static const EdgeInsets pagePadding = EdgeInsets.symmetric(horizontal: lg, vertical: lg);
  static const EdgeInsets cardPadding = EdgeInsets.all(lg);
  static const EdgeInsets buttonPadding = EdgeInsets.symmetric(horizontal: xxl, vertical: lg);

  // Soft Shadows
  static const List<BoxShadow> softShadow = [
    BoxShadow(
      color: Color(0x0A000000),
      blurRadius: 16,
      offset: Offset(0, 6),
    ),
  ];

  static const List<BoxShadow> cardShadow = [
    BoxShadow(
      color: Color(0x0F000000),
      blurRadius: 12,
      offset: Offset(0, 4),
    ),
  ];
}
