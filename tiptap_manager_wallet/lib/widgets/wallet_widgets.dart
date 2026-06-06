import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/wallet_models.dart';
import '../theme/app_theme.dart';

class GlassCard extends StatelessWidget {
  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.borderColor,
  });

  final Widget child;
  final EdgeInsets padding;
  final Color? borderColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: padding,
      decoration: BoxDecoration(
        color: AppTheme.surface.withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: borderColor ?? AppTheme.border),
      ),
      child: child,
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
                color: color ?? AppTheme.textPrimary,
              ),
    );
  }
}

class SummaryStatTile extends StatelessWidget {
  const SummaryStatTile({
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
    return GlassCard(
      padding: const EdgeInsets.all(16),
      borderColor: accent?.withValues(alpha: 0.35),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label.toUpperCase(),
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  color: accent ?? AppTheme.textMuted,
                  letterSpacing: 1.1,
                  fontWeight: FontWeight.w700,
                ),
          ),
          const SizedBox(height: 8),
          MoneyText(
            amount: amount,
            symbol: symbol,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w800,
                  color: accent ?? AppTheme.textPrimary,
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
                  fontWeight: FontWeight.w700,
                ),
          ),
          const SizedBox(height: 12),
          if (rows.isEmpty)
            const Text('No data yet.', style: TextStyle(color: AppTheme.textMuted))
          else
            ...rows.map(
              (row) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        row.label,
                        style: const TextStyle(
                          color: AppTheme.textSecondary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    Text(
                      '${row.count}',
                      style: const TextStyle(color: AppTheme.textMuted),
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
