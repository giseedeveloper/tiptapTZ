import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Responsive spacing & width tokens for manager wallet screens.
class WalletLayout {
  WalletLayout._();

  static double screenWidth(BuildContext context) => MediaQuery.sizeOf(context).width;

  static double horizontalPadding(BuildContext context) {
    final width = screenWidth(context);
    if (width >= 840) {
      return 48;
    }
    if (width >= 600) {
      return 32;
    }
    if (width >= 390) {
      return 20;
    }

    return 16;
  }

  static double maxContentWidth(BuildContext context) {
    final width = screenWidth(context);
    if (width >= 840) {
      return 560;
    }
    if (width >= 600) {
      return 520;
    }

    return width;
  }

  static double bottomScrollPadding(BuildContext context) {
    return MediaQuery.paddingOf(context).bottom + 108;
  }

  static bool compactWidth(BuildContext context) => screenWidth(context) < 380;

  static bool tabletWidth(BuildContext context) => screenWidth(context) >= 600;

  static EdgeInsets pageInsets(BuildContext context, {double top = 8}) {
    final horizontal = horizontalPadding(context);

    return EdgeInsets.fromLTRB(
      horizontal,
      top,
      horizontal,
      bottomScrollPadding(context),
    );
  }
}

class WalletPageFrame extends StatelessWidget {
  const WalletPageFrame({
    super.key,
    required this.child,
    this.scrollable = true,
  });

  final Widget child;
  final bool scrollable;

  @override
  Widget build(BuildContext context) {
    final frame = SafeArea(
      bottom: false,
      child: Align(
        alignment: Alignment.topCenter,
        child: ConstrainedBox(
          constraints: BoxConstraints(
            maxWidth: WalletLayout.maxContentWidth(context),
          ),
          child: child,
        ),
      ),
    );

    if (!scrollable) {
      return frame;
    }

    return frame;
  }
}

class ScreenTitle extends StatelessWidget {
  const ScreenTitle({
    super.key,
    required this.title,
    required this.subtitle,
  });

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    final compact = WalletLayout.compactWidth(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.w800,
                letterSpacing: -0.4,
                fontSize: compact ? 22 : null,
              ),
        ),
        const SizedBox(height: 4),
        Text(
          subtitle,
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            color: AppTheme.textMutedOf(context),
            fontSize: compact ? 12 : 13,
            fontWeight: FontWeight.w500,
            height: 1.35,
          ),
        ),
      ],
    );
  }
}

class PayoutProfileHero extends StatelessWidget {
  const PayoutProfileHero({
    super.key,
    required this.name,
    required this.email,
    this.restaurant,
  });

  final String name;
  final String email;
  final String? restaurant;

  @override
  Widget build(BuildContext context) {
    final compact = WalletLayout.compactWidth(context);
    final padding = compact ? 16.0 : 20.0;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(padding),
      decoration: BoxDecoration(
        gradient: AppTheme.heroGradient,
        borderRadius: BorderRadius.circular(compact ? 20 : 24),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryDark.withValues(alpha: 0.3),
            blurRadius: 24,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned(
            top: -18,
            right: -10,
            child: Container(
              width: compact ? 72 : 96,
              height: compact ? 72 : 96,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.07),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'MANAGER',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.65),
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.4,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: compact ? 17 : 19,
                  letterSpacing: -0.3,
                  height: 1.15,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                email,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.78),
                  fontSize: compact ? 12 : 13,
                  height: 1.3,
                ),
              ),
              if (restaurant != null && restaurant!.isNotEmpty) ...[
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.14),
                    ),
                  ),
                  child: Text(
                    restaurant!,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.lavender.withValues(alpha: 0.95),
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      height: 1.3,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

class PayoutMethodSelector extends StatelessWidget {
  const PayoutMethodSelector({
    super.key,
    required this.selectedMethod,
    required this.onChanged,
    required this.methodCardBuilder,
  });

  final String selectedMethod;
  final ValueChanged<String> onChanged;
  final Widget Function({
    required String title,
    required String subtitle,
    required IconData icon,
    required bool selected,
    required VoidCallback onTap,
  }) methodCardBuilder;

  @override
  Widget build(BuildContext context) {
    final stackVertically = WalletLayout.compactWidth(context);

    final mobileCard = methodCardBuilder(
      title: 'Mobile Money',
      subtitle: stackVertically ? 'M-Pesa' : 'M-Pesa & mobile wallets',
      icon: Icons.phone_android_rounded,
      selected: selectedMethod == 'Mobile Money',
      onTap: () => onChanged('Mobile Money'),
    );

    final bankCard = methodCardBuilder(
      title: 'Bank',
      subtitle: stackVertically ? 'Bank transfer' : 'Local bank transfer',
      icon: Icons.account_balance_rounded,
      selected: selectedMethod == 'Bank Transfer',
      onTap: () => onChanged('Bank Transfer'),
    );

    if (stackVertically) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          mobileCard,
          const SizedBox(height: 12),
          bankCard,
        ],
      );
    }

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Expanded(child: mobileCard),
          const SizedBox(width: 12),
          Expanded(child: bankCard),
        ],
      ),
    );
  }
}
