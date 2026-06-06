import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

enum WalletNavTab { wallet, activity, payout, settings }

class WalletNavIcon extends StatelessWidget {
  const WalletNavIcon({
    super.key,
    required this.tab,
    required this.selected,
    required this.label,
    this.compact = false,
  });

  final WalletNavTab tab;
  final bool selected;
  final String label;
  final bool compact;

  IconData get _icon {
    switch (tab) {
      case WalletNavTab.wallet:
        return selected ? Icons.account_balance_wallet_rounded : Icons.account_balance_wallet_outlined;
      case WalletNavTab.activity:
        return selected ? Icons.receipt_long_rounded : Icons.receipt_long_outlined;
      case WalletNavTab.payout:
        return selected ? Icons.savings_rounded : Icons.savings_outlined;
      case WalletNavTab.settings:
        return selected ? Icons.settings_rounded : Icons.settings_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        AnimatedContainer(
          duration: const Duration(milliseconds: 240),
          curve: Curves.easeOutCubic,
          width: selected ? (compact ? 44 : 50) : (compact ? 38 : 42),
          height: selected ? (compact ? 44 : 50) : (compact ? 38 : 42),
          decoration: BoxDecoration(
            gradient: selected ? AppTheme.brandGradient : null,
            color: selected ? null : Colors.transparent,
            borderRadius: BorderRadius.circular(selected ? 18 : 14),
            border: selected
                ? null
                : Border.all(color: AppTheme.glassBorderOf(context)),
            boxShadow: selected
                ? [
                    BoxShadow(
                      color: AppTheme.primaryDark.withValues(alpha: 0.35),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ]
                : null,
          ),
          child: Icon(
            _icon,
            size: selected ? (compact ? 22 : 24) : (compact ? 19 : 21),
            color: selected ? Colors.white : AppTheme.navUnselectedOf(context),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          label,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontSize: compact ? 9 : 10,
            fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
            letterSpacing: 0.1,
            color: selected
                ? AppTheme.navLabelSelectedOf(context)
                : AppTheme.navLabelUnselectedOf(context),
          ),
        ),
      ],
    );
  }
}

enum TransactionIconKind { incoming, outgoing, profile, wallet, emptyPayments, emptyWithdrawals }

class BrandIconBadge extends StatelessWidget {
  const BrandIconBadge({
    super.key,
    required this.kind,
    this.size = 48,
    this.accent,
    this.onGradient = false,
  });

  final TransactionIconKind kind;
  final double size;
  final Color? accent;
  final bool onGradient;

  IconData get _icon {
    switch (kind) {
      case TransactionIconKind.incoming:
        return Icons.arrow_downward_rounded;
      case TransactionIconKind.outgoing:
        return Icons.arrow_upward_rounded;
      case TransactionIconKind.profile:
        return Icons.person_rounded;
      case TransactionIconKind.wallet:
        return Icons.account_balance_wallet_rounded;
      case TransactionIconKind.emptyPayments:
        return Icons.payments_rounded;
      case TransactionIconKind.emptyWithdrawals:
        return Icons.outbound_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    final highlight = accent ?? AppTheme.primary;
    final radius = size * 0.34;

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        gradient: onGradient
            ? null
            : LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  highlight.withValues(alpha: 0.28),
                  highlight.withValues(alpha: 0.12),
                ],
              ),
        color: onGradient ? Colors.white.withValues(alpha: 0.16) : null,
        borderRadius: BorderRadius.circular(radius),
        border: Border.all(
          color: onGradient
              ? Colors.white.withValues(alpha: 0.22)
              : highlight.withValues(alpha: 0.22),
        ),
      ),
      child: Icon(
        _icon,
        size: size * 0.46,
        color: Colors.white,
      ),
    );
  }
}
