import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'auth_theme.dart';

/// Matches web `auth-email-divider` — light border + muted label.
class AuthEmailDivider extends StatelessWidget {
  final String label;

  const AuthEmailDivider({
    super.key,
    this.label = 'Or continue with email',
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          const Expanded(child: Divider(color: AuthTheme.border, height: 1)),
          Container(
            color: AuthTheme.cardBg,
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Text(
              label,
              style: GoogleFonts.poppins(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: AuthTheme.textSecondary,
              ),
            ),
          ),
          const Expanded(child: Divider(color: AuthTheme.border, height: 1)),
        ],
      ),
    );
  }
}
