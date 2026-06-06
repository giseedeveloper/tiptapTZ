import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class PayoutProfileScreen extends StatefulWidget {
  const PayoutProfileScreen({super.key});

  @override
  State<PayoutProfileScreen> createState() => _PayoutProfileScreenState();
}

class _PayoutProfileScreenState extends State<PayoutProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _detailsController;
  String _method = 'Mobile Money';
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final profile = context.read<WalletProvider>().snapshot?.payoutProfile;
    _method = profile?.payoutMethod ?? 'Mobile Money';
    _detailsController = TextEditingController(text: profile?.payoutDetails ?? '');
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (context.read<WalletProvider>().snapshot == null) {
        context.read<WalletProvider>().loadSummary();
      }
    });
  }

  @override
  void dispose() {
    _detailsController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _saving = true);
    try {
      await context.read<WalletProvider>().savePayoutProfile(
            method: _method,
            details: _detailsController.text.trim(),
          );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Payout profile saved.')),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message)),
      );
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Payout profile')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                auth.session?.user.name ?? 'Manager',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w800,
                    ),
              ),
              const SizedBox(height: 4),
              Text(
                auth.session?.user.email ?? '',
                style: const TextStyle(color: AppTheme.textMuted),
              ),
              const SizedBox(height: 24),
              const Text(
                'Save your M-Pesa or bank details once. They will be used when you request a withdrawal.',
                style: TextStyle(color: AppTheme.textSecondary, height: 1.5),
              ),
              const SizedBox(height: 20),
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
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Account / phone details',
                  hintText: 'M-Pesa number, bank account, account name…',
                ),
                validator: (value) =>
                    (value == null || value.trim().isEmpty) ? 'Required' : null,
              ),
              const SizedBox(height: 28),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _saving ? null : _save,
                  child: _saving
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Save profile'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
