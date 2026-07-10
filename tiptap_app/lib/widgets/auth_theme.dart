import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Web guest-layout palette (`guest-layout.blade.php`).
class AuthTheme {
  static const Color bgTop = Color(0xFFDDD7FE);
  static const Color bgMid = Color(0xFFF5F3FF);
  static const Color bgBottom = Color(0xFFFFFFFF);
  static const Color pageBg = Color(0xFFFAFBFC);
  static const Color textPrimary = Color(0xFF12141C);
  static const Color textSecondary = Color(0xFF64708B);
  static const Color purple = Color(0xFF8C71F6);
  static const Color purpleDark = Color(0xFF6D52E8);
  static const Color border = Color(0xFFE8E8ED);
  static const Color lavender = Color(0xFFEDE9FE);
  static const Color lavenderSoft = Color(0xFFDDD7FE);
  static const Color cardBg = Color(0xEBFFFFFF);
  static const Color error = Color(0xFFEF4444);
  static const Color errorBg = Color(0xFFFFF1F2);
  static const Color errorBorder = Color(0xFFFECDD3);

  static const LinearGradient heroGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [purple, purpleDark],
  );

  static const LinearGradient pageGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    stops: [0.0, 0.35, 0.72],
    colors: [bgTop, bgMid, bgBottom],
  );

  static const LinearGradient brandGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [purple, purpleDark],
  );

  static TextStyle titleStyle({double size = 24}) => GoogleFonts.poppins(
        fontSize: size,
        fontWeight: FontWeight.w900,
        color: textPrimary,
        height: 1.15,
        letterSpacing: -0.3,
      );

  static TextStyle subtitleStyle({double size = 13}) => GoogleFonts.poppins(
        fontSize: size,
        fontWeight: FontWeight.w500,
        color: textSecondary,
        height: 1.45,
      );

  static TextStyle labelStyle() => GoogleFonts.poppins(
        fontSize: 13,
        fontWeight: FontWeight.w600,
        color: textPrimary,
      );

  static TextStyle linkStyle({double size = 13}) => GoogleFonts.poppins(
        fontSize: size,
        fontWeight: FontWeight.w600,
        color: purpleDark,
      );

  static BoxDecoration glassCardDecoration({double radius = 24}) {
    return BoxDecoration(
      color: cardBg,
      borderRadius: BorderRadius.circular(radius),
      border: Border.all(color: purple.withValues(alpha: 0.15)),
      boxShadow: [
        BoxShadow(
          color: purpleDark.withValues(alpha: 0.1),
          blurRadius: 24,
          offset: const Offset(0, 8),
        ),
        BoxShadow(
          color: textPrimary.withValues(alpha: 0.04),
          blurRadius: 32,
          offset: const Offset(0, 12),
        ),
      ],
    );
  }

  static InputDecoration inputDecoration({
    required String hint,
    required IconData icon,
    Widget? suffixIcon,
    bool compact = false,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: GoogleFonts.poppins(
        color: textSecondary.withValues(alpha: 0.55),
        fontSize: 13,
      ),
      prefixIcon: Icon(
        icon,
        color: textSecondary.withValues(alpha: 0.55),
        size: 18,
      ),
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: Colors.white,
      isDense: true,
      contentPadding: EdgeInsets.symmetric(
        horizontal: 14,
        vertical: compact ? 11 : 12,
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: purple.withValues(alpha: 0.55), width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: error),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: error, width: 1.5),
      ),
      errorStyle: GoogleFonts.poppins(fontSize: 10, color: error),
    );
  }

  static Widget primaryButton({
    required String label,
    required VoidCallback? onPressed,
    bool loading = false,
    bool compact = false,
    bool expanded = true,
  }) {
    final button = Container(
      height: compact ? 46 : 48,
      decoration: BoxDecoration(
        gradient: heroGradient,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: purpleDark.withValues(alpha: 0.35),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          padding: EdgeInsets.zero,
        ),
        child: loading
            ? const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: Colors.white,
                ),
              )
            : Text(
                label,
                style: GoogleFonts.poppins(
                  fontSize: compact ? 14 : 15,
                  fontWeight: FontWeight.w700,
                ),
              ),
      ),
    );

    return expanded ? SizedBox(width: double.infinity, child: button) : button;
  }

  static Widget errorBanner(String message) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: errorBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: errorBorder),
      ),
      child: Text(
        message,
        style: GoogleFonts.poppins(
          color: const Color(0xFFBE123C),
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }
}

/// Web-style auth page: lavender gradient, brand header, glass card.
class AuthPageShell extends StatelessWidget {
  final Widget child;
  final bool showBrandHeader;
  final Widget? leading;
  final Widget? footer;

  const AuthPageShell({
    super.key,
    required this.child,
    this.showBrandHeader = true,
    this.leading,
    this.footer,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AuthTheme.pageBg,
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          const DecoratedBox(
            decoration: BoxDecoration(gradient: AuthTheme.pageGradient),
            child: SizedBox.expand(),
          ),
          Positioned(
            top: -100,
            right: -70,
            child: _BlurBlob(
              size: 320,
              color: AuthTheme.purple.withValues(alpha: 0.28),
            ),
          ),
          Positioned(
            bottom: 40,
            left: -90,
            child: _BlurBlob(
              size: 260,
              color: AuthTheme.lavenderSoft.withValues(alpha: 0.55),
            ),
          ),
          SafeArea(
            child: Column(
              children: [
                if (leading != null)
                  Align(alignment: Alignment.centerLeft, child: leading!),
                Expanded(
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(20, 8, 20, 16),
                    child: Column(
                      children: [
                        if (showBrandHeader) ...[
                          const AuthBrandHeader(),
                          const SizedBox(height: 16),
                        ],
                        child,
                        if (footer != null) ...[
                          const SizedBox(height: 16),
                          footer!,
                        ],
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class AuthBrandHeader extends StatelessWidget {
  const AuthBrandHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: 48,
          height: 48,
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(
            gradient: AuthTheme.brandGradient,
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: AuthTheme.purple.withValues(alpha: 0.28),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.asset(
              'assets/images/logo.png',
              fit: BoxFit.contain,
            ),
          ),
        ),
        const SizedBox(width: 10),
        RichText(
          text: TextSpan(
            style: GoogleFonts.poppins(
              fontSize: 20,
              fontWeight: FontWeight.w900,
              color: AuthTheme.textPrimary,
              letterSpacing: -0.5,
            ),
            children: [
              const TextSpan(text: 'TIP'),
              TextSpan(
                text: 'TAP',
                style: GoogleFonts.poppins(
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  foreground: Paint()
                    ..shader = AuthTheme.brandGradient.createShader(
                      const Rect.fromLTWH(0, 0, 60, 24),
                    ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class AuthGlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;

  const AuthGlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
  });

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
        child: Container(
          width: double.infinity,
          padding: padding,
          decoration: AuthTheme.glassCardDecoration(),
          child: Stack(
            children: [
              Positioned(
                top: -30,
                right: -30,
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AuthTheme.purple.withValues(alpha: 0.08),
                  ),
                ),
              ),
              Positioned(
                bottom: -30,
                left: -30,
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AuthTheme.lavenderSoft.withValues(alpha: 0.45),
                  ),
                ),
              ),
              child,
            ],
          ),
        ),
      ),
    );
  }
}

class AuthTextField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final String hint;
  final IconData icon;
  final TextInputType? keyboardType;
  final bool obscure;
  final bool compact;
  final Widget? suffixIcon;
  final String? Function(String?)? validator;

  const AuthTextField({
    super.key,
    required this.controller,
    required this.label,
    required this.hint,
    required this.icon,
    this.keyboardType,
    this.obscure = false,
    this.compact = false,
    this.suffixIcon,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AuthTheme.labelStyle()),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: obscure,
          style: GoogleFonts.poppins(
            color: AuthTheme.textPrimary,
            fontSize: 14,
            fontWeight: FontWeight.w500,
          ),
          decoration: AuthTheme.inputDecoration(
            hint: hint,
            icon: icon,
            suffixIcon: suffixIcon,
            compact: compact,
          ),
          validator: validator,
        ),
      ],
    );
  }
}

class _BlurBlob extends StatelessWidget {
  final double size;
  final Color color;

  const _BlurBlob({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return ImageFiltered(
      imageFilter: ImageFilter.blur(sigmaX: 50, sigmaY: 50),
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(shape: BoxShape.circle, color: color),
      ),
    );
  }
}
