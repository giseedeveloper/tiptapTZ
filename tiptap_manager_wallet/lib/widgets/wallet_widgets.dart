import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/wallet_models.dart';
import '../providers/settings_provider.dart';
import '../theme/app_theme.dart';
import 'app_icons.dart';
import 'screen_layout.dart';

class PortalBackground extends StatelessWidget {
  const PortalBackground({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Stack(
      children: [
        Container(
          decoration: BoxDecoration(
            gradient: isDark
                ? const LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [AppTheme.bg, Color(0xFF0C0A12)],
                  )
                : AppTheme.lightWashGradient,
          ),
        ),
        Positioned(
          top: -80,
          right: -60,
          child: _GlowOrb(
            color: AppTheme.primary.withValues(alpha: isDark ? 0.22 : 0.14),
            size: 220,
          ),
        ),
        Positioned(
          top: 120,
          left: -90,
          child: _GlowOrb(
            color: AppTheme.primaryDark.withValues(alpha: isDark ? 0.16 : 0.1),
            size: 180,
          ),
        ),
        if (!isDark)
          Positioned(
            bottom: -40,
            right: -30,
            child: _GlowOrb(
              color: AppTheme.lavender.withValues(alpha: 0.35),
              size: 140,
            ),
          ),
        child,
      ],
    );
  }
}

class _GlowOrb extends StatelessWidget {
  const _GlowOrb({required this.color, required this.size});

  final Color color;
  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(color: color, blurRadius: 80, spreadRadius: 20),
        ],
      ),
    );
  }
}

class GlassCard extends StatelessWidget {
  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.borderColor,
    this.gradient,
  });

  final Widget child;
  final EdgeInsets padding;
  final Color? borderColor;
  final Gradient? gradient;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: isDark ? 12 : 6, sigmaY: isDark ? 12 : 6),
        child: Container(
          width: double.infinity,
          padding: padding,
          decoration: BoxDecoration(
            gradient: gradient,
            color: gradient == null
                ? (isDark ? AppTheme.glass : AppTheme.lightSurface.withValues(alpha: 0.94))
                : null,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(
              color: borderColor ?? AppTheme.glassBorderOf(context),
            ),
            boxShadow: AppTheme.cardShadowOf(context),
          ),
          child: child,
        ),
      ),
    );
  }
}

class BalanceHeroCard extends StatelessWidget {
  const BalanceHeroCard({
    super.key,
    required this.amount,
    required this.symbol,
    required this.commissionRate,
    this.restaurantName,
  });

  final double amount;
  final String symbol;
  final double commissionRate;
  final String? restaurantName;

  @override
  Widget build(BuildContext context) {
    final formatter = NumberFormat('#,##0', 'en');

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: AppTheme.heroGradient,
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryDark.withValues(alpha: 0.45),
            blurRadius: 32,
            offset: const Offset(0, 16),
          ),
        ],
      ),
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned(
            top: -24,
            right: -16,
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.08),
              ),
            ),
          ),
          Positioned(
            bottom: -30,
            left: -20,
            child: Container(
              width: 90,
              height: 90,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 24, 24, 28),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.14),
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
                      ),
                      child: Text(
                        'AVAILABLE BALANCE',
                        style: Theme.of(context).textTheme.labelSmall?.copyWith(
                              color: Colors.white.withValues(alpha: 0.92),
                              letterSpacing: 1.4,
                              fontWeight: FontWeight.w800,
                              fontSize: 10,
                            ),
                      ),
                    ),
                    const Spacer(),
                    const BrandIconBadge(
                      kind: TransactionIconKind.wallet,
                      size: 36,
                      onGradient: true,
                    ),
                  ],
                ),
                const SizedBox(height: 18),
                Text(
                  '$symbol ${formatter.format(amount)}',
                  style: Theme.of(context).textTheme.displaySmall?.copyWith(
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: -0.8,
                        height: 1.05,
                      ),
                ),
                const SizedBox(height: 10),
                Text(
                  'Net after ${commissionRate.toStringAsFixed(0)}% platform fee',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.78),
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (restaurantName != null && restaurantName!.isNotEmpty) ...[
                  const SizedBox(height: 14),
                  Text(
                    restaurantName!,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.lavender.withValues(alpha: 0.95),
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class QuickActionButton extends StatelessWidget {
  const QuickActionButton({
    super.key,
    required this.icon,
    required this.label,
    required this.onTap,
    this.primary = true,
    this.enabled = true,
  });

  final IconData icon;
  final String label;
  final VoidCallback? onTap;
  final bool primary;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    if (primary) {
      return Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: enabled ? onTap : null,
          borderRadius: BorderRadius.circular(18),
          child: Ink(
            height: 54,
            decoration: BoxDecoration(
              color: enabled ? Colors.white : Colors.white.withValues(alpha: 0.35),
              borderRadius: BorderRadius.circular(18),
              boxShadow: enabled
                  ? [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.15),
                        blurRadius: 12,
                        offset: const Offset(0, 6),
                      ),
                    ]
                  : null,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon, color: AppTheme.primaryDeep, size: 20),
                const SizedBox(width: 8),
                Text(
                  label,
                  style: const TextStyle(
                    color: AppTheme.primaryDeep,
                    fontWeight: FontWeight.w800,
                    fontSize: 15,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return OutlinedButton.icon(
      onPressed: enabled ? onTap : null,
      icon: Icon(icon, size: 18),
      label: Text(label),
    );
  }
}

class MoneyText extends StatelessWidget {
  const MoneyText({
    super.key,
    required this.amount,
    required this.symbol,
    this.style,
    this.color,
  });

  final double amount;
  final String symbol;
  final TextStyle? style;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final formatter = NumberFormat('#,##0', 'en');
    return Text(
      '$symbol ${formatter.format(amount)}',
      style: style ??
          Theme.of(context).textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.w800,
                color: color ?? AppTheme.textPrimaryOf(context),
              ),
    );
  }
}

class StatPill extends StatelessWidget {
  const StatPill({
    super.key,
    required this.label,
    required this.amount,
    required this.symbol,
    this.accent,
  });

  final String label;
  final double amount;
  final String symbol;
  final Color? accent;

  @override
  Widget build(BuildContext context) {
    final color = accent ?? AppTheme.textPrimaryOf(context);

    return Container(
      width: 148,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surfaceOf(context).withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: (accent ?? AppTheme.primary).withValues(alpha: 0.2),
        ),
        boxShadow: AppTheme.cardShadowOf(context),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label.toUpperCase(),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              color: accent?.withValues(alpha: 0.85) ?? AppTheme.textMutedOf(context),
              fontSize: 10,
              fontWeight: FontWeight.w800,
              letterSpacing: 1,
              height: 1.3,
            ),
          ),
          const SizedBox(height: 10),
          MoneyText(
            amount: amount,
            symbol: symbol,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w800,
                  color: color,
                ),
          ),
        ],
      ),
    );
  }
}

class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.title,
    this.actionLabel,
    this.onAction,
  });

  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.2,
                ),
          ),
          const Spacer(),
          if (actionLabel != null && onAction != null)
            TextButton(
              onPressed: onAction,
              style: TextButton.styleFrom(
                foregroundColor: AppTheme.primary,
                padding: EdgeInsets.zero,
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: Text(
                actionLabel!,
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
              ),
            ),
        ],
      ),
    );
  }
}

class PayoutMethodCard extends StatelessWidget {
  const PayoutMethodCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.selected,
    required this.onTap,
    this.compact = false,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: EdgeInsets.all(compact ? 14 : 16),
          decoration: BoxDecoration(
            gradient: selected ? AppTheme.brandGradient : null,
            color: selected
                ? null
                : AppTheme.surfaceElevatedOf(context).withValues(alpha: 0.85),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: selected
                  ? AppTheme.primary.withValues(alpha: 0.5)
                  : AppTheme.glassBorderOf(context),
            ),
            boxShadow: selected
                ? [
                    BoxShadow(
                      color: AppTheme.primaryDark.withValues(alpha: 0.25),
                      blurRadius: 16,
                      offset: const Offset(0, 8),
                    ),
                  ]
                : AppTheme.cardShadowOf(context),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: selected
                      ? Colors.white.withValues(alpha: 0.18)
                      : AppTheme.primary.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color: selected ? Colors.white : AppTheme.primaryDark,
                  size: 22,
                ),
              ),
              const SizedBox(height: 14),
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: selected ? Colors.white : AppTheme.textPrimaryOf(context),
                  fontWeight: FontWeight.w800,
                  fontSize: compact ? 14 : 15,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: selected
                      ? Colors.white.withValues(alpha: 0.78)
                      : AppTheme.textMutedOf(context),
                  fontSize: compact ? 11 : 12,
                  height: 1.35,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class PayoutStatusBanner extends StatelessWidget {
  const PayoutStatusBanner({
    super.key,
    required this.isComplete,
    this.method,
    this.details,
  });

  final bool isComplete;
  final String? method;
  final String? details;

  @override
  Widget build(BuildContext context) {
    final accent = isComplete ? AppTheme.success : AppTheme.amber;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: accent.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: accent.withValues(alpha: 0.28)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: accent.withValues(alpha: 0.16),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              isComplete ? Icons.verified_rounded : Icons.info_outline_rounded,
              color: accent,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  isComplete ? 'Payout profile active' : 'Complete your payout setup',
                  style: TextStyle(
                    color: accent,
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                if (isComplete) ...[
                  if (method != null && method!.isNotEmpty)
                    Text(
                      method!,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: AppTheme.textSecondaryOf(context),
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  if (details != null && details!.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(
                      details!,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: AppTheme.textSecondaryOf(context),
                        fontSize: 12,
                        height: 1.45,
                      ),
                    ),
                  ],
                ] else
                  Text(
                    'Add payout details so withdrawals can be processed faster.',
                    style: TextStyle(
                      color: AppTheme.textSecondaryOf(context),
                      fontSize: 12,
                      height: 1.45,
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

class ActivityListTile extends StatelessWidget {
  const ActivityListTile({
    super.key,
    required this.title,
    required this.subtitle,
    required this.amount,
    required this.symbol,
    this.kind = TransactionIconKind.incoming,
    this.amountColor,
  });

  final String title;
  final String subtitle;
  final double amount;
  final String symbol;
  final TransactionIconKind kind;
  final Color? amountColor;

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          BrandIconBadge(kind: kind),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                ),
                const SizedBox(height: 3),
                Text(
                  subtitle,
                  style: TextStyle(
                    color: AppTheme.textMutedOf(context),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          MoneyText(
            amount: amount,
            symbol: symbol,
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                  fontWeight: FontWeight.w800,
                  color: amountColor ?? AppTheme.success,
                ),
          ),
        ],
      ),
    );
  }
}

class BreakdownTable extends StatelessWidget {
  const BreakdownTable({
    super.key,
    required this.title,
    required this.rows,
    required this.symbol,
  });

  final String title;
  final List<BreakdownRow> rows;
  final String symbol;

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w800,
                ),
          ),
          const SizedBox(height: 12),
          if (rows.isEmpty)
            Text('No data yet.', style: TextStyle(color: AppTheme.textMutedOf(context)))
          else
            ...rows.map(
              (row) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        row.label,
                        style: TextStyle(
                          color: AppTheme.textSecondaryOf(context),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    Text(
                      '${row.count}',
                      style: TextStyle(color: AppTheme.textMutedOf(context)),
                    ),
                    const SizedBox(width: 16),
                    MoneyText(
                      amount: row.total,
                      symbol: symbol,
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w700,
                          ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class FloatingNavBar extends StatelessWidget {
  const FloatingNavBar({
    super.key,
    required this.index,
    required this.onChanged,
  });

  final int index;
  final ValueChanged<int> onChanged;

  static const _tabs = WalletNavTab.values;

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.paddingOf(context).bottom;
    final strings = context.watch<SettingsProvider>().strings;
    const compact = true;

    return Container(
      margin: EdgeInsets.fromLTRB(
        WalletLayout.horizontalPadding(context),
        0,
        WalletLayout.horizontalPadding(context),
        bottomInset > 0 ? bottomInset + 6 : 22,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
      decoration: BoxDecoration(
        color: AppTheme.navBarSurfaceOf(context),
        borderRadius: BorderRadius.circular(30),
        border: Border.all(color: AppTheme.glassBorderOf(context)),
        boxShadow: AppTheme.navBarShadowOf(context),
      ),
      child: Row(
        children: List.generate(_tabs.length, (i) {
          final tab = _tabs[i];
          return Expanded(
            child: GestureDetector(
              onTap: () => onChanged(i),
              behavior: HitTestBehavior.opaque,
              child: WalletNavIcon(
                tab: tab,
                selected: index == i,
                label: strings.labelForTab(tab),
                compact: compact,
              ),
            ),
          );
        }),
      ),
    );
  }
}
