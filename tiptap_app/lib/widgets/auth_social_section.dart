import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/config.dart';
import 'auth_theme.dart';

/// Social sign-in row aligned with web `auth-social-providers`.
class AuthSocialSection extends StatelessWidget {
  final String intent;
  final String role;
  final bool compact;

  const AuthSocialSection({
    super.key,
    this.intent = 'login',
    this.role = 'waiter',
    this.compact = false,
  });

  String get _heading => intent == 'register' ? 'Sign up with' : 'Sign in with';

  Future<void> _openProvider(BuildContext context, String provider) async {
    if (provider != 'google') {
      // Match web: buttons look active but non-Google providers are not wired yet.
      return;
    }

    final uri = Uri.parse(
      '${AppConfig.webBaseUrl}/auth/$provider/redirect'
      '?role=$role&intent=$intent',
    );

    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!context.mounted) return;

    if (launched) {
      _showSnack(
        context,
        'Complete sign-in in your browser, then return here and use your email & password.',
        isInfo: true,
      );
    } else {
      _showSnack(context, 'Could not open browser. Try email sign-in instead.');
    }
  }

  void _showSnack(BuildContext context, String message, {bool isInfo = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: GoogleFonts.poppins(fontSize: 13)),
        backgroundColor: isInfo ? AuthTheme.purpleDark : AuthTheme.error,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
        duration: const Duration(seconds: 5),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    const providers = ['google', 'facebook', 'apple'];
    final buttonSize = compact ? 44.0 : 48.0;
    final iconSize = compact ? 22.0 : 24.0;

    return Column(
      children: [
        Text(_heading, style: AuthTheme.subtitleStyle(size: compact ? 12 : 13)),
        SizedBox(height: compact ? 10 : 12),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: providers.map((p) {
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 6),
              child: _ProviderCircle(
                provider: p,
                size: buttonSize,
                iconSize: iconSize,
                onTap: () => _openProvider(context, p),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}

class _ProviderCircle extends StatelessWidget {
  final String provider;
  final double size;
  final double iconSize;
  final VoidCallback onTap;

  const _ProviderCircle({
    required this.provider,
    required this.size,
    required this.iconSize,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isApple = provider == 'apple';

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        customBorder: const CircleBorder(),
        splashColor: AuthTheme.purple.withValues(alpha: 0.08),
        highlightColor: AuthTheme.purple.withValues(alpha: 0.04),
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Colors.white,
            border: Border.all(color: AuthTheme.border),
            boxShadow: [
              BoxShadow(
                color: AuthTheme.textPrimary.withValues(alpha: 0.06),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          alignment: Alignment.center,
          child: _providerIcon(provider, iconSize, isApple),
        ),
      ),
    );
  }

  Widget _providerIcon(String provider, double size, bool isApple) {
    final asset = switch (provider) {
      'google' => 'assets/icons/social/google.svg',
      'facebook' => 'assets/icons/social/facebook.svg',
      'apple' => 'assets/icons/social/apple.svg',
      _ => null,
    };
    if (asset == null) return const SizedBox.shrink();

    return SvgPicture.asset(
      asset,
      width: size,
      height: size,
      fit: BoxFit.contain,
      colorFilter: isApple
          ? const ColorFilter.mode(AuthTheme.textPrimary, BlendMode.srcIn)
          : null,
    );
  }
}
