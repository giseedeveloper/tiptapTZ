import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../theme/app_theme.dart';
import '../widgets/wallet_widgets.dart';
import 'login_screen.dart';
import 'withdraw_screen.dart';

class WalletHomeScreen extends StatefulWidget {
  const WalletHomeScreen({super.key});

  @override
  State<WalletHomeScreen> createState() => _WalletHomeScreenState();
}

class _WalletHomeScreenState extends State<WalletHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<WalletProvider>().loadSummary();
    });
  }

  Future<void> _refresh() async {
    await context.read<WalletProvider>().refreshAll();
  }

  @override
  Widget build(BuildContext context) {
    final wallet = context.watch<WalletProvider>();
    final auth = context.watch<AuthProvider>();
    final snapshot = wallet.snapshot;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Wallet'),
            if (auth.session?.user.restaurantName != null)
              Text(
                auth.session!.user.restaurantName!,
                style: Theme.of(context).textTheme.labelSmall?.copyWith(
                      color: AppTheme.textMuted,
                    ),
              ),
          ],
        ),
        actions: [
          IconButton(
            onPressed: () async {
              await auth.logout();
              if (!context.mounted) return;
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            icon: const Icon(Icons.logout_rounded),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        color: AppTheme.primary,
        child: wallet.loadingSummary && snapshot == null
            ? ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 180),
                  Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                ],
              )
            : ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
                children: [
                  if (wallet.summaryError != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Text(
                        wallet.summaryError!,
                        style: const TextStyle(color: AppTheme.rose),
                      ),
                    ),
                  if (snapshot != null) ...[
                    GlassCard(
                      borderColor: AppTheme.primary.withValues(alpha: 0.35),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'AVAILABLE BALANCE',
                            style: Theme.of(context).textTheme.labelSmall?.copyWith(
                                  color: AppTheme.primary,
                                  letterSpacing: 1.2,
                                  fontWeight: FontWeight.w800,
                                ),
                          ),
                          const SizedBox(height: 12),
                          MoneyText(
                            amount: snapshot.summary.availableBalance,
                            symbol: snapshot.currencySymbol,
                            style: Theme.of(context).textTheme.displaySmall?.copyWith(
                                  fontWeight: FontWeight.w900,
                                  color: AppTheme.success,
                                ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Net after ${snapshot.summary.commissionRate.toStringAsFixed(0)}% platform fee',
                            style: const TextStyle(color: AppTheme.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: snapshot.summary.availableBalance <= 0
                                ? null
                                : () async {
                                    await Navigator.of(context).push(
                                      MaterialPageRoute(
                                        builder: (_) => const WithdrawScreen(),
                                      ),
                                    );
                                  },
                            icon: const Icon(Icons.arrow_upward_rounded),
                            label: const Text('Withdraw'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 1.35,
                      children: [
                        SummaryStatTile(
                          label: 'Gross received',
                          amount: snapshot.summary.totalEarned,
                          symbol: snapshot.currencySymbol,
                        ),
                        SummaryStatTile(
                          label: 'Platform fee',
                          amount: snapshot.summary.platformCommission,
                          symbol: snapshot.currencySymbol,
                          accent: AppTheme.rose,
                        ),
                        SummaryStatTile(
                          label: 'Net earnings',
                          amount: snapshot.summary.netEarned,
                          symbol: snapshot.currencySymbol,
                        ),
                        SummaryStatTile(
                          label: 'Pending',
                          amount: snapshot.summary.pendingWithdrawals,
                          symbol: snapshot.currencySymbol,
                          accent: AppTheme.amber,
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
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
                ],
              ),
      ),
    );
  }
}
