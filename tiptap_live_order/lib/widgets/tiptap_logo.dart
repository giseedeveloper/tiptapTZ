import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Official TIPTAP mark from `public/images/logo.png`.
class TiptapLogo extends StatelessWidget {
  const TiptapLogo({
    super.key,
    this.size = 120,
    this.fit = BoxFit.contain,
    this.onDark = false,
    this.showPlate = true,
  });

  final double size;
  final BoxFit fit;
  final bool onDark;
  final bool showPlate;

  static const String assetPath = 'assets/images/logo.png';

  @override
  Widget build(BuildContext context) {
    final isDark = onDark || Theme.of(context).brightness == Brightness.dark;
    final image = Image.asset(
      assetPath,
      width: size,
      height: size,
      fit: fit,
      filterQuality: FilterQuality.high,
    );

    if (!showPlate) {
      return SizedBox(width: size, height: size, child: image);
    }

    return Container(
      width: size,
      height: size,
      padding: EdgeInsets.all(size * 0.12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(size * 0.22),
        boxShadow: [
          BoxShadow(
            color: isDark
                ? Colors.black.withValues(alpha: 0.35)
                : AppTheme.primaryDark.withValues(alpha: 0.12),
            blurRadius: isDark ? 28 : 20,
            offset: Offset(0, isDark ? 12 : 8),
          ),
          if (!isDark)
            BoxShadow(
              color: AppTheme.primary.withValues(alpha: 0.08),
              blurRadius: 32,
              spreadRadius: 2,
            ),
        ],
        border: Border.all(
          color: isDark
              ? Colors.white.withValues(alpha: 0.08)
              : AppTheme.primary.withValues(alpha: 0.06),
        ),
      ),
      child: image,
    );
  }
}
