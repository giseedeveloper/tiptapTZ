import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../theme/app_theme.dart';
import '../widgets/app_icons.dart';
import '../widgets/screen_layout.dart';
import '../widgets/wallet_widgets.dart';

class ActivityScreen extends StatefulWidget {
  const ActivityScreen({super.key});

  @override
  State<ActivityScreen> createState() => _ActivityScreenState();
}

class _ActivityScreenState extends State<ActivityScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final wallet = context.read<WalletProvider>();
      wallet.loadPayments(refresh: true);
      wallet.loadWithdrawals(refresh: true);
    });
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final wallet = context.watch<WalletProvider>();
    final symbol = wallet.snapshot?.currencySymbol ?? 'Tsh';
    final dateFmt = DateFormat('d MMM, HH:mm');

    return SafeArea(
      bottom: false,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: EdgeInsets.fromLTRB(
              WalletLayout.horizontalPadding(context),
              8,
              WalletLayout.horizontalPadding(context),
              0,
            ),
            child: Text(
              'Activity',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.4,
                  ),
            ),
          ),
        Padding(
          padding: EdgeInsets.fromLTRB(
            WalletLayout.horizontalPadding(context),
            16,
            WalletLayout.horizontalPadding(context),
            0,
          ),
          child: Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: AppTheme.surfaceOf(context).withValues(alpha: 0.92),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.glassBorderOf(context)),
            ),
            child: TabBar(
              controller: _tabs,
              indicator: BoxDecoration(
                gradient: AppTheme.brandGradient,
                borderRadius: BorderRadius.circular(12),
              ),
              indicatorSize: TabBarIndicatorSize.tab,
              dividerColor: Colors.transparent,
              labelColor: Colors.white,
              unselectedLabelColor: AppTheme.navUnselectedOf(context),
              labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
              tabs: const [
                Tab(text: 'Payments'),
                Tab(text: 'Withdrawals'),
              ],
            ),
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabs,
            children: [
              RefreshIndicator(
                onRefresh: () => wallet.loadPayments(refresh: true),
                color: AppTheme.primary,
                child: _buildPaymentsList(wallet, symbol, dateFmt),
              ),
              RefreshIndicator(
                onRefresh: () => wallet.loadWithdrawals(refresh: true),
                color: AppTheme.primary,
                child: _buildWithdrawalsList(wallet, symbol, dateFmt),
              ),
            ],
          ),
        ),
      ],
      ),
    );
  }

  Widget _buildPaymentsList(
    WalletProvider wallet,
    String symbol,
    DateFormat dateFmt,
  ) {
    if (wallet.loadingPayments && wallet.payments.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: const [
          SizedBox(height: 120),
          Center(child: CircularProgressIndicator(color: AppTheme.primary)),
        ],
      );
    }

    if (wallet.payments.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        children: [
          const SizedBox(height: 80),
          GlassCard(
            padding: const EdgeInsets.all(28),
            child: Column(
              children: [
                const BrandIconBadge(kind: TransactionIconKind.emptyPayments, size: 56),
                const SizedBox(height: 12),
                Text(
                  'No payments yet',
                  style: TextStyle(
                    color: AppTheme.textSecondaryOf(context),
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Customer payments will show up here.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppTheme.textMutedOf(context)),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.fromLTRB(
        WalletLayout.horizontalPadding(context),
        16,
        WalletLayout.horizontalPadding(context),
        WalletLayout.bottomScrollPadding(context),
      ),
      itemCount: wallet.payments.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final payment = wallet.payments[index];
        return ActivityListTile(
          title: payment.paymentType,
          subtitle: [
            if (payment.method != null) payment.method,
            if (payment.createdAt != null) dateFmt.format(payment.createdAt!.toLocal()),
          ].whereType<String>().join(' · '),
          amount: payment.amount,
          symbol: symbol,
          kind: TransactionIconKind.incoming,
        );
      },
    );
  }

  Widget _buildWithdrawalsList(
    WalletProvider wallet,
    String symbol,
    DateFormat dateFmt,
  ) {
    if (wallet.loadingWithdrawals && wallet.withdrawals.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: const [
          SizedBox(height: 120),
          Center(child: CircularProgressIndicator(color: AppTheme.primary)),
        ],
      );
    }

    if (wallet.withdrawals.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        children: [
          const SizedBox(height: 80),
          GlassCard(
            padding: const EdgeInsets.all(28),
            child: Column(
              children: [
                const BrandIconBadge(kind: TransactionIconKind.emptyWithdrawals, size: 56),
                const SizedBox(height: 12),
                Text(
                  'No withdrawals yet',
                  style: TextStyle(
                    color: AppTheme.textSecondaryOf(context),
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.fromLTRB(
        WalletLayout.horizontalPadding(context),
        16,
        WalletLayout.horizontalPadding(context),
        WalletLayout.bottomScrollPadding(context),
      ),
      itemCount: wallet.withdrawals.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final item = wallet.withdrawals[index];
        final statusColor = AppTheme.withdrawalStatusColor(item.status);

        return GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  BrandIconBadge(
                    kind: TransactionIconKind.outgoing,
                    accent: statusColor,
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        MoneyText(
                          amount: item.amount,
                          symbol: symbol,
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.w800,
                              ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          [
                            item.paymentMethod,
                            if (item.createdAt != null)
                              dateFmt.format(item.createdAt!.toLocal()),
                          ].whereType<String>().join(' · '),
                          style: TextStyle(
                            color: AppTheme.textMutedOf(context),
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.14),
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(color: statusColor.withValues(alpha: 0.35)),
                    ),
                    child: Text(
                      item.status.toUpperCase(),
                      style: TextStyle(
                        color: statusColor,
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ],
              ),
              if (item.adminNote != null && item.adminNote!.isNotEmpty) ...[
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.rose.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppTheme.rose.withValues(alpha: 0.2)),
                  ),
                  child: Text(
                    item.adminNote!,
                    style: const TextStyle(color: AppTheme.rose, fontSize: 12, height: 1.4),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}
