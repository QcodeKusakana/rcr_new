import 'package:flutter/material.dart';

/// Identité visuelle RCR (identique au site : bleu encre, or, papier).
class Rcr {
  static const Color ink = Color(0xFF1B2A44);
  static const Color ink2 = Color(0xFF233355);
  static const Color paper = Color(0xFFF1E9D8);
  static const Color paper2 = Color(0xFFE7DBBF);
  static const Color line = Color(0xFFCBBB92);
  static const Color gold = Color(0xFF9C7A2E);
  static const Color gold2 = Color(0xFFC9A24A);
  static const Color red = Color(0xFF7D2330);
  static const Color green = Color(0xFF1E7A3C);
  static const Color soft = Color(0xFF5B5346);

  static ThemeData theme() {
    final scheme = ColorScheme.fromSeed(seedColor: ink, primary: ink, secondary: gold, surface: Colors.white).copyWith(error: red);
    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: const Color(0xFFF7F3EA),
      appBarTheme: const AppBarTheme(
        backgroundColor: ink, foregroundColor: Colors.white, elevation: 0, centerTitle: false,
        titleTextStyle: TextStyle(fontSize: 19, fontWeight: FontWeight.w700, color: Colors.white, letterSpacing: 0.2),
      ),
      snackBarTheme: SnackBarThemeData(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
      dividerTheme: const DividerThemeData(color: line, thickness: 0.8),
      listTileTheme: const ListTileThemeData(iconColor: ink),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: line)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: line)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: gold, width: 2)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: ink,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: ink,
          minimumSize: const Size.fromHeight(50),
          side: const BorderSide(color: ink),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14), side: const BorderSide(color: line)),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        indicatorColor: paper2,
        labelTextStyle: WidgetStateProperty.all(const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
      ),
    );
  }
}
