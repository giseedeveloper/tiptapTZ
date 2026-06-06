import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class WithdrawScreen extends StatefulWidget {
  const WithdrawScreen({super.key});

  @override
  State<WithdrawScreen> createState() => _WithdrawScreenState();
}

class _WithdrawScreenState extends State<WithdrawScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _detailsController = TextEditingController();
  String _method = 'Mobile Money';
  bool _useSaved = true;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    final snapshot = context.read<WalletProvider>().snapshot;
    if (snapshot?.payoutProfile.isComplete == true) {
      _useSaved = true;
    } else {
      _useSaved = false;
    }
  }

  @override
  void dispose() {
    _amountController.dispose();
    _detailsController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final wallet = context.read<WalletProvider>();
    final snapshot = wallet.snapshot;
    if (snapshot == null) return;

    setState(() => _submitting = true);

    try {
      await wallet.submitWithdrawal(
        amount: double.parse(_amountController.text),
        useSavedPayout: _useSaved && snapshot.payoutProfile.isComplete,
        paymentMethod: _useSaved ? null : _method,
        paymentDetails: _useSaved ? null : _detailsController.text.trim(),
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Withdrawal request submitted.')),
      );
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      final amountError = e.errors?['amount'];
      final message = amountError is List && amountError.isNotEmpty
          ? amountError.first.toString()
          : e.message;
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Could not submit withdrawal.')),
      );
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final snapshot = context.watch<WalletProvider>().snapshot;

    return Scaffold(
      appBar: AppBar(title: const Text('Request withdrawal')),
      body: snapshot == null
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Available: ${snapshot.currencySymbol} ${snapshot.summary.availableBalance.toStringAsFixed(0)}',
                      style: const TextStyle(color: AppTheme.textSecondary),
                    ),
                    Text(
                      'Minimum: ${snapshot.currencySymbol} ${snapshot.minWithdrawal.toStringAsFixed(0)}',
                      style: const TextStyle(color: AppTheme.textMuted),
                    ),
                    const SizedBox(height: 20),
                    TextFormField(
                      controller: _amountController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Amount (${snapshot.currencySymbol})',
                      ),
                      validator: (value) {
                        final parsed = double.tryParse(value ?? '');
                        if (parsed == null || parsed <= 0) {
                          return 'Enter a valid amount';
                        }
                        if (parsed < snapshot.minWithdrawal) {
                          return 'Minimum is ${snapshot.minWithdrawal.toStringAsFixed(0)}';
                        }
                        if (parsed > snapshot.summary.availableBalance) {
                          return 'Exceeds available balance';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    if (snapshot.payoutProfile.isComplete) ...[
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        value: _useSaved,
                        onChanged: (value) => setState(() => _useSaved = value),
                        title: const Text('Use saved payout profile'),
                        subtitle: Text(
                          '${snapshot.payoutProfile.payoutMethod} · ${snapshot.payoutProfile.payoutDetails}',
                          style: const TextStyle(color: AppTheme.textMuted),
                        ),
                      ),
                      const SizedBox(height: 8),
                    ],
                    if (!_useSaved || !snapshot.payoutProfile.isComplete) ...[
                      DropdownButtonFormField<String>(
                        value: _method,
                        decoration: const InputDecoration(labelText: 'Payout method'),
                        items: const [
                          DropdownMenuItem(value: 'Mobile Money', child: Text('Mobile Money')),
                          DropdownMenuItem(value: 'Bank Transfer', child: Text('Bank Transfer')),
                        ],
                        onChanged: (value) {
                          if (value != null) setState(() => _method = value);
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _detailsController,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          labelText: 'Account / phone details',
                        ),
                        validator: (value) =>
                            (value == null || value.trim().isEmpty) ? 'Required' : null,
                      ),
                    ],
                    const SizedBox(height: 28),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _submitting ? null : _submit,
                        child: _submitting
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Text('Submit request'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
