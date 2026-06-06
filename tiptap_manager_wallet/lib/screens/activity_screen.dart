import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../theme/app_theme.dart';
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

    return Scaffold(
      appBar: AppBar(
        title: const Text('Activity'),
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: AppTheme.primary,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.textMuted,
          tabs: const [
            Tab(text: 'Payments'),
            Tab(text: 'Withdrawals'),
          ],
        ),
      ),
      body: TabBarView(
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
        children: const [
          SizedBox(height: 120),
          Center(child: Text('No payments yet', style: TextStyle(color: AppTheme.textMuted))),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      itemCount: wallet.payments.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final payment = wallet.payments[index];
        return GlassCard(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.arrow_downward_rounded, color: AppTheme.success),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      payment.paymentType,
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                    Text(
                      [
                        if (payment.method != null) payment.method,
                        if (payment.createdAt != null) dateFmt.format(payment.createdAt!.toLocal()),
                      ].whereType<String>().join(' · '),
                      style: const TextStyle(color: AppTheme.textMuted, fontSize: 12),
                    ),
                  ],
                ),
              ),
              MoneyText(
                amount: payment.amount,
                symbol: symbol,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppTheme.success,
                    ),
              ),
            ],
          ),
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
        children: const [
          SizedBox(height: 120),
          Center(child: Text('No withdrawals yet', style: TextStyle(color: AppTheme.textMuted))),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
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
                  Expanded(
                    child: MoneyText(
                      amount: item.amount,
                      symbol: symbol,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w800,
                          ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
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
              const SizedBox(height: 6),
              Text(
                [
                  item.paymentMethod,
                  if (item.createdAt != null) dateFmt.format(item.createdAt!.toLocal()),
                ].whereType<String>().join(' · '),
                style: const TextStyle(color: AppTheme.textMuted, fontSize: 12),
              ),
              if (item.adminNote != null && item.adminNote!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  item.adminNote!,
                  style: const TextStyle(color: AppTheme.rose, fontSize: 12),
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}
