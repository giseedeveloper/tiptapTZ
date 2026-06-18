import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/config.dart';
import '../l10n/app_strings.dart';
import '../providers/app_providers.dart';
import '../providers/settings_provider.dart';
import '../theme/app_theme.dart';
import '../widgets/screen_layout.dart';
import '../widgets/wallet_widgets.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  bool get _isSouthAfricaApi => AppConfig.apiBaseUrl.contains('.co.za');

  String _marketLabel(AppStrings s) => _isSouthAfricaApi ? s.marketSa : s.marketTz;

  Future<void> _confirmLogout(BuildContext context) async {
    final s = context.read<SettingsProvider>().strings;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(s.logoutConfirmTitle),
        content: Text(s.logoutConfirmBody),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(s.cancel)),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(s.confirm, style: const TextStyle(color: AppTheme.rose)),
          ),
        ],
      ),
    );

    if (confirmed != true || !context.mounted) {
      return;
    }

    await context.read<AuthProvider>().logout();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final wallet = context.watch<WalletProvider>();
    final settings = context.watch<SettingsProvider>();
    final s = settings.strings;
    final snapshot = wallet.snapshot;
    final user = auth.session?.user;

    return WalletPageFrame(
      child: CustomScrollView(
        physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics(),
        ),
        slivers: [
          SliverPadding(
            padding: WalletLayout.pageInsets(context, top: 4),
            sliver: SliverToBoxAdapter(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ScreenTitle(
                    title: s.settingsTitle,
                    subtitle: s.settingsSubtitle,
                  ),
                  const SizedBox(height: 16),
                  PayoutProfileHero(
                    name: user?.name ?? s.managerRole,
                    email: user?.email ?? '—',
                    restaurant: user?.restaurantName ?? snapshot?.restaurantName,
                  ),
                  const SizedBox(height: 20),
                  _SectionLabel(text: s.restaurantSection),
                  const SizedBox(height: 10),
                  GlassCard(
                    child: Column(
                      children: [
                        _InfoTile(
                          icon: Icons.storefront_rounded,
                          label: s.restaurantName,
                          value: snapshot?.restaurantName ?? user?.restaurantName ?? '—',
                        ),
                        Divider(height: 24, color: AppTheme.glassBorderOf(context)),
                        _InfoTile(
                          icon: Icons.payments_rounded,
                          label: s.currency,
                          value: snapshot?.currencySymbol ?? '—',
                        ),
                        if (snapshot != null) ...[
                          Divider(height: 24, color: AppTheme.glassBorderOf(context)),
                          _InfoTile(
                            icon: Icons.percent_rounded,
                            label: s.platformFee,
                            value: '${snapshot.summary.commissionRate.toStringAsFixed(0)}%',
                          ),
                          Divider(height: 24, color: AppTheme.glassBorderOf(context)),
                          _InfoTile(
                            icon: Icons.account_balance_wallet_outlined,
                            label: s.availableBalance,
                            value:
                                '${snapshot.currencySymbol} ${snapshot.summary.availableBalance.toStringAsFixed(0)}',
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  _SectionLabel(text: s.managerSection),
                  const SizedBox(height: 10),
                  GlassCard(
                    child: Column(
                      children: [
                        _InfoTile(
                          icon: Icons.person_outline_rounded,
                          label: s.managerRole,
                          value: user?.name ?? '—',
                        ),
                        Divider(height: 24, color: AppTheme.glassBorderOf(context)),
                        _InfoTile(
                          icon: Icons.email_outlined,
                          label: s.email,
                          value: user?.email ?? '—',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  _SectionLabel(text: s.preferencesSection),
                  const SizedBox(height: 10),
                  GlassCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          s.languageLabel,
                          style: TextStyle(
                            color: AppTheme.textSecondaryOf(context),
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: AppLanguage.values.map((lang) {
                            return _ChoiceChip(
                              label: lang.label(s),
                              selected: settings.language == lang,
                              onTap: () => settings.setLanguage(lang),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 20),
                        Text(
                          s.themeLabel,
                          style: TextStyle(
                            color: AppTheme.textSecondaryOf(context),
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _ChoiceChip(
                                label: s.themeDark,
                                icon: Icons.dark_mode_rounded,
                                selected: settings.themeMode == ThemeMode.dark,
                                onTap: () => settings.setThemeMode(ThemeMode.dark),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: _ChoiceChip(
                                label: s.themeLight,
                                icon: Icons.light_mode_rounded,
                                selected: settings.themeMode == ThemeMode.light,
                                onTap: () => settings.setThemeMode(ThemeMode.light),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  _SectionLabel(text: s.aboutSection),
                  const SizedBox(height: 10),
                  GlassCard(
                    child: Column(
                      children: [
                        _InfoTile(
                          icon: Icons.public_rounded,
                          label: s.marketRegion,
                          value: _marketLabel(s),
                        ),
                        Divider(height: 24, color: AppTheme.glassBorderOf(context)),
                        const _InfoTile(
                          icon: Icons.info_outline_rounded,
                          label: 'Version',
                          value: '1.0.0',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  OutlinedButton.icon(
                    onPressed: () => _confirmLogout(context),
                    icon: const Icon(Icons.logout_rounded, color: AppTheme.rose),
                    label: Text(
                      s.logout,
                      style: const TextStyle(
                        color: AppTheme.rose,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size.fromHeight(54),
                      side: BorderSide(color: AppTheme.rose.withValues(alpha: 0.35)),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18),
                      ),
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

class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text.toUpperCase(),
      style: TextStyle(
        color: AppTheme.textMutedOf(context),
        fontSize: 11,
        fontWeight: FontWeight.w800,
        letterSpacing: 1.2,
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  const _InfoTile({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: AppTheme.primary),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: AppTheme.textMutedOf(context),
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: AppTheme.textPrimaryOf(context),
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                  height: 1.35,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ChoiceChip extends StatelessWidget {
  const _ChoiceChip({
    required this.label,
    required this.selected,
    required this.onTap,
    this.icon,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            gradient: selected ? AppTheme.brandGradient : null,
            color: selected ? null : AppTheme.surfaceElevatedOf(context).withValues(alpha: 0.65),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: selected
                  ? AppTheme.primary.withValues(alpha: 0.45)
                  : AppTheme.glassBorderOf(context),
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (icon != null) ...[
                Icon(icon, size: 16, color: selected ? Colors.white : AppTheme.primary),
                const SizedBox(width: 6),
              ],
              Flexible(
                child: Text(
                  label,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: selected ? Colors.white : AppTheme.textPrimaryOf(context),
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
