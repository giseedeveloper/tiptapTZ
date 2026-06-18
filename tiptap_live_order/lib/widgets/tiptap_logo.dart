import 'package:flutter/material.dart';

/// Official tap. brand mark on purple (`assets/images/logo.png`).
class TiptapLogo extends StatelessWidget {
  const TiptapLogo({
    super.key,
    this.size = 120,
    this.fit = BoxFit.contain,
    this.onDark = false,
    this.showPlate = false,
  });

  final double size;
  final BoxFit fit;
  final bool onDark;
  /// Logo is already a full square icon — plate off by default.
  final bool showPlate;

  static const String assetPath = 'assets/images/logo.png';
  static const Color brandPurple = Color(0xFF4B2C7F);

  @override
  Widget build(BuildContext context) {
    final isDark = onDark || Theme.of(context).brightness == Brightness.dark;
    final image = ClipRRect(
      borderRadius: BorderRadius.circular(size * 0.22),
      child: Image.asset(
        assetPath,
        width: size,
        height: size,
        fit: fit,
        filterQuality: FilterQuality.high,
      ),
    );

    if (!showPlate) {
      return Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(size * 0.22),
          boxShadow: [
            BoxShadow(
              color: brandPurple.withValues(alpha: isDark ? 0.55 : 0.35),
              blurRadius: isDark ? 32 : 24,
              offset: Offset(0, isDark ? 14 : 10),
            ),
            if (isDark)
              BoxShadow(
                color: Colors.white.withValues(alpha: 0.06),
                blurRadius: 12,
                spreadRadius: -2,
              ),
          ],
        ),
        child: image,
      );
    }

    return Container(
      width: size,
      height: size,
      padding: EdgeInsets.all(size * 0.04),
      decoration: BoxDecoration(
        color: brandPurple,
        borderRadius: BorderRadius.circular(size * 0.22),
        boxShadow: [
          BoxShadow(
            color: brandPurple.withValues(alpha: 0.4),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: image,
    );
  }
}
