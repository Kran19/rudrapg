import 'package:flutter/material.dart';

/// Global Color System for Rudra Group PG
class AppColors {
  AppColors._();

  // Brand Palette
  static const Color primary = Color(0xFF0F172A); // Deep Slate Navy
  static const Color secondary = Color(0xFF2563EB); // Royal Blue
  static const Color accent = Color(0xFF14B8A6); // Teal
  
  // Status Colors
  static const Color success = Color(0xFF16A34A); // Vibrant Green
  static const Color warning = Color(0xFFF59E0B); // Amber
  static const Color error = Color(0xFFDC2626); // Crimson Red
  static const Color info = Color(0xFF3B82F6); // Soft Blue

  // Canvas & Surfaces
  static const Color background = Color(0xFFF8FAFC); // Off-white canvas
  static const Color card = Color(0xFFFFFFFF); // Pure white surface
  static const Color divider = Color(0xFFE5E7EB); // Soft grey divider
  
  // Text Neutral Colors
  static const Color textPrimary = Color(0xFF111827); // Dark neutral
  static const Color textSecondary = Color(0xFF6B7280); // Muted neutral
  static const Color textMuted = Color(0xFF9CA3AF); // Light neutral

  // Interactive Bed Status Colors
  static const Color bedAvailable = Color(0xFF16A34A);
  static const Color bedOccupied = Color(0xFFDC2626);
  static const Color bedReserved = Color(0xFFF59E0B);
  static const Color bedSelected = Color(0xFF2563EB);
  
  // Gradients
  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}
