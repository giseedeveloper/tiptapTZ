import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../providers/settings_provider.dart';
import '../theme/app_theme.dart';
import '../widgets/app_icons.dart';
import '../widgets/screen_layout.dart';
import '../widgets/wallet_widgets.dart';
import 'withdraw_screen.dart';

class WalletHomeScreen extends StatefulWidget {
  const WalletHomeScreen({super.key, this.onOpenActivity});

  final VoidCallback? onOpenActivity;

  @override
  State<WalletHomeScreen> createState() => _WalletHomeScreenState();
}

class _WalletHomeScreenState extends State<WalletHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final wallet = context.read<WalletProvider>();
      await wallet.loadSummary();
      await wallet.loadPayments(refresh: true);
    });
  }

  Future<void> _refresh() async {
    await context.read<WalletProvider>().refreshAll();
  }

  @override
  Widget build(BuildContext context) {
    final wallet = context.watch<WalletProvider>();
    final auth = context.watch<AuthProvider>();
    final s = context.watch<SettingsProvider>().strings;
    final snapshot = wallet.snapshot;
    final dateFmt = DateFormat('d MMM, HH:mm');
    final firstName = auth.session?.user.name.split(' ').first ?? s.managerRole;
    final horizontal = WalletLayout.horizontalPadding(context);

    return SafeArea(
      bottom: false,
      child: RefreshIndicator(
      onRefresh: _refresh,
      color: AppTheme.primary,
      edgeOffset: 72,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(
          parent: BouncingScrollPhysics(),
        ),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.fromLTRB(horizontal, 12, horizontal, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    s.hi(firstName),
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.4,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    s.venueWallet,
                    style: TextStyle(
                      color: AppTheme.textMutedOf(context),
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (wallet.loadingSummary && snapshot == null)
            const SliverFillRemaining(
              hasScrollBody: false,
              child: Center(
                child: CircularProgressIndicator(color: AppTheme.primary),
              ),
            )
          else if (snapshot != null) ...[
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (wallet.summaryError != null)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(
                          wallet.summaryError!,
                          style: const TextStyle(color: AppTheme.rose),
                        ),
                      ),
                    BalanceHeroCard(
                      amount: snapshot.summary.availableBalance,
                      symbol: snapshot.currencySymbol,
                      commissionRate: snapshot.summary.commissionRate,
                      restaurantName: auth.session?.user.restaurantName,
                    ),
                    const SizedBox(height: 16),
                    QuickActionButton(
                      icon: Icons.north_east_rounded,
                      label: s.withdrawFunds,
                      enabled: snapshot.summary.availableBalance > 0,
                      onTap: () async {
                        await Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const WithdrawScreen()),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 24, left: 20),
                child: SectionHeader(title: s.overview),
              ),
            ),
            SliverToBoxAdapter(
              child: SizedBox(
                height: 118,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  physics: const BouncingScrollPhysics(),
                  children: [
                    StatPill(
                      label: 'Gross received',
                      amount: snapshot.summary.totalEarned,
                      symbol: snapshot.currencySymbol,
                    ),
                    const SizedBox(width: 12),
                    StatPill(
                      label: 'Platform fee',
                      amount: snapshot.summary.platformCommission,
                      symbol: snapshot.currencySymbol,
                      accent: AppTheme.rose,
                    ),
                    const SizedBox(width: 12),
                    StatPill(
                      label: 'Net earnings',
                      amount: snapshot.summary.netEarned,
                      symbol: snapshot.currencySymbol,
                      accent: AppTheme.lavender,
                    ),
                    const SizedBox(width: 12),
                    StatPill(
                      label: 'Pending',
                      amount: snapshot.summary.pendingWithdrawals,
                      symbol: snapshot.currencySymbol,
                      accent: AppTheme.amber,
                    ),
                  ],
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
                child: SectionHeader(
                  title: s.recentPayments,
                  actionLabel: wallet.payments.isNotEmpty ? s.seeAll : null,
                  onAction: wallet.payments.isNotEmpty ? widget.onOpenActivity : null,
                ),
              ),
            ),
            if (wallet.payments.isEmpty)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: GlassCard(
                    padding: const EdgeInsets.all(24),
                    child: Center(
                      child: Text(
                        'No payments yet — earnings appear here when customers pay.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: AppTheme.textMutedOf(context),
                          height: 1.5,
                        ),
                      ),
                    ),
                  ),
                ),
              )
            else
              SliverList.separated(
                itemCount: wallet.payments.take(4).length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final payment = wallet.payments[index];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: ActivityListTile(
                      title: payment.paymentType,
                      subtitle: [
                        if (payment.method != null) payment.method,
                        if (payment.createdAt != null)
                          dateFmt.format(payment.createdAt!.toLocal()),
                      ].whereType<String>().join(' · '),
                      amount: payment.amount,
                      symbol: snapshot.currencySymbol,
                      kind: TransactionIconKind.incoming,
                    ),
                  );
                },
              ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
                child: Column(
                  children: [
                    BreakdownTable(
                      title: 'Earnings by type',
                      rows: snapshot.breakdown.byType,
                      symbol: snapshot.currencySymbol,
                    ),
                    const SizedBox(height: 12),
                    BreakdownTable(
                      title: 'Earnings by method',
                      rows: snapshot.breakdown.byMethod,
                      symbol: snapshot.currencySymbol,
                    ),
                  ],
                ),
              ),
            ),
          ],
          const SliverToBoxAdapter(child: SizedBox(height: 96)),
        ],
      ),
      ),
    );
  }
}
