import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// TIPTAP portal / fin brand — matches `portal-theme.blade.php` & `app.css`.
class AppTheme {
  static const Color primary = Color(0xFF8C71F6);
  static const Color primaryDark = Color(0xFF6D52E8);
  static const Color primaryDeep = Color(0xFF5B3FD6);
  static const Color lavender = Color(0xFFDDD7FE);
  static const Color light = Color(0xFFEDE9FE);
  static const Color mist = Color(0xFFF5F3FF);

  static const Color success = Color(0xFF34D399);
  static const Color rose = Color(0xFFF43F5E);
  static const Color amber = Color(0xFFFBBF24);

  static const Color bg = Color(0xFF12101C);
  static const Color surface = Color(0xFF1C1828);
  static const Color surfaceElevated = Color(0xFF2A2540);
  static const Color glass = Color(0xA61C1828);
  static const Color glassBorder = Color(0x0FFFFFFF);
  static const Color accentGlow = Color(0x598C71F6);

  static const Color textPrimary = Color(0xFFF8FAFC);
  static const Color textSecondary = Color(0xB3FFFFFF);
  static const Color textMuted = Color(0x80FFFFFF);

  static const LinearGradient brandGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primary, primaryDark, primaryDeep],
  );

  static const LinearGradient heroGradient = LinearGradient(
    begin: Alignment(-0.8, -1),
    end: Alignment(1.2, 1),
    colors: [
      Color(0xFF9B82FF),
      Color(0xFF6D52E8),
      Color(0xFF4C1D95),
    ],
    stops: [0.0, 0.45, 1.0],
  );

  static List<BoxShadow> get cardGlow => [
        BoxShadow(
          color: primary.withValues(alpha: 0.12),
          blurRadius: 32,
          offset: const Offset(0, 12),
        ),
      ];

  static ThemeData get dark {
    final base = GoogleFonts.interTextTheme(
      ThemeData(brightness: Brightness.dark).textTheme,
    );

    return ThemeData(
      brightness: Brightness.dark,
      scaffoldBackgroundColor: bg,
      colorScheme: const ColorScheme.dark(
        primary: primary,
        secondary: primaryDark,
        surface: surface,
        error: rose,
        onPrimary: Colors.white,
        onSurface: textPrimary,
      ),
      textTheme: base.apply(
        bodyColor: textPrimary,
        displayColor: textPrimary,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.inter(
          color: textPrimary,
          fontSize: 20,
          fontWeight: FontWeight.w700,
          letterSpacing: -0.3,
        ),
        iconTheme: const IconThemeData(color: textPrimary),
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: glassBorder),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surfaceElevated,
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: glassBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: glassBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: primary, width: 1.5),
        ),
        labelStyle: const TextStyle(color: textSecondary),
        hintStyle: const TextStyle(color: textMuted),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          elevation: 0,
          minimumSize: const Size.fromHeight(54),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
          textStyle: GoogleFonts.inter(
            fontWeight: FontWeight.w700,
            fontSize: 16,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: textPrimary,
          side: const BorderSide(color: glassBorder),
          minimumSize: const Size.fromHeight(54),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
        ),
      ),
      tabBarTheme: const TabBarThemeData(
        indicatorColor: primary,
        labelColor: primary,
        unselectedLabelColor: textMuted,
        dividerColor: Colors.transparent,
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: surfaceElevated,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    );
  }

  /// Light-mode surfaces — soft violet wash, not flat grey.
  static const Color lightBg = Color(0xFFF8F6FF);
  static const Color lightSurface = Color(0xFFFFFFFF);
  static const Color lightSurfaceElevated = Color(0xFFF3F0FF);
  static const Color lightBorder = Color(0x1A6D52E8);

  static const LinearGradient lightWashGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFFFDFCFF),
      Color(0xFFF5F3FF),
      Color(0xFFEDE9FE),
    ],
    stops: [0.0, 0.55, 1.0],
  );

  static ThemeData get lightTheme {
    final base = GoogleFonts.interTextTheme(
      ThemeData(brightness: Brightness.light).textTheme,
    );

    return ThemeData(
      brightness: Brightness.light,
      scaffoldBackgroundColor: lightBg,
      colorScheme: const ColorScheme.light(
        primary: primaryDark,
        secondary: primary,
        surface: lightSurface,
        error: rose,
        onPrimary: Colors.white,
        onSurface: finInk,
      ),
      textTheme: base.apply(
        bodyColor: finInk,
        displayColor: finInk,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        titleTextStyle: GoogleFonts.inter(
          color: finInk,
          fontSize: 20,
          fontWeight: FontWeight.w700,
        ),
        iconTheme: const IconThemeData(color: finInk),
      ),
      dividerTheme: DividerThemeData(
        color: finInk.withValues(alpha: 0.08),
        thickness: 1,
      ),
      cardTheme: CardThemeData(
        color: lightSurface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: BorderSide(color: finInk.withValues(alpha: 0.07)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: lightSurface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: finInk.withValues(alpha: 0.1)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: finInk.withValues(alpha: 0.1)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: primary, width: 1.5),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryDark,
          foregroundColor: Colors.white,
          elevation: 0,
          minimumSize: const Size.fromHeight(54),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: finInk,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    );
  }

  static const Color finSurface = Color(0xFFF4F6FA);
  static const Color finInk = Color(0xFF12141C);

  static Color textPrimaryOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? textPrimary : finInk;

  static Color textMutedOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? textMuted
          : finInk.withValues(alpha: 0.55);

  static Color textSecondaryOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? textSecondary
          : finInk.withValues(alpha: 0.72);

  static Color surfaceOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? surface : Colors.white;

  static Color navBarSurfaceOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? surface.withValues(alpha: 0.96)
          : Colors.white.withValues(alpha: 0.96);

  static Color backgroundOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? bg : lightBg;

  static Color glassBorderOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? glassBorder
          : finInk.withValues(alpha: 0.08);

  static Color surfaceElevatedOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? surfaceElevated
          : lightSurfaceElevated;

  static Color navUnselectedOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? textMuted
          : finInk.withValues(alpha: 0.42);

  static Color navLabelUnselectedOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
          ? textMuted
          : finInk.withValues(alpha: 0.5);

  static Color navLabelSelectedOf(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? lavender : primaryDark;

  static List<BoxShadow> cardShadowOf(BuildContext context) {
    if (Theme.of(context).brightness == Brightness.dark) {
      return cardGlow;
    }

    return [
      BoxShadow(
        color: primaryDark.withValues(alpha: 0.06),
        blurRadius: 24,
        offset: const Offset(0, 10),
      ),
      BoxShadow(
        color: finInk.withValues(alpha: 0.04),
        blurRadius: 6,
        offset: const Offset(0, 2),
      ),
    ];
  }

  static List<BoxShadow> navBarShadowOf(BuildContext context) {
    if (Theme.of(context).brightness == Brightness.dark) {
      return [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.25),
          blurRadius: 24,
          offset: const Offset(0, 8),
        ),
      ];
    }

    return [
      BoxShadow(
        color: primaryDark.withValues(alpha: 0.1),
        blurRadius: 28,
        offset: const Offset(0, 12),
      ),
      BoxShadow(
        color: finInk.withValues(alpha: 0.06),
        blurRadius: 8,
        offset: const Offset(0, 2),
      ),
    ];
  }

  static Color withdrawalStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'approved':
      case 'paid':
        return success;
      case 'rejected':
        return rose;
      case 'pending':
        return amber;
      default:
        return textMuted;
    }
  }
}
