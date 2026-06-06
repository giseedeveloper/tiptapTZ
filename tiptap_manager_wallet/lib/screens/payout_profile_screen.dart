import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/wallet_models.dart';
import '../providers/app_providers.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/screen_layout.dart';
import '../widgets/wallet_widgets.dart';

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
  bool _hydratedFromApi = false;

  static const _methods = ['Mobile Money', 'Bank Transfer'];

  @override
  void initState() {
    super.initState();
    _detailsController = TextEditingController();
    _hydrateFromSnapshot(context.read<WalletProvider>().snapshot?.payoutProfile);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (context.read<WalletProvider>().snapshot == null) {
        context.read<WalletProvider>().loadSummary();
      }
    });
  }

  void _hydrateFromSnapshot(PayoutProfile? profile) {
    if (profile == null || _hydratedFromApi) {
      return;
    }

    final method = profile.payoutMethod;
    if (method != null && _methods.contains(method)) {
      _method = method;
    }

    final details = profile.payoutDetails;
    if (details != null && details.isNotEmpty) {
      _detailsController.text = details;
    }

    _hydratedFromApi = true;
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

  String get _detailsLabel {
    return _method == 'Bank Transfer'
        ? 'Bank account details'
        : 'Mobile money number';
  }

  String get _detailsHint {
    return _method == 'Bank Transfer'
        ? 'Bank name, account number, account holder name'
        : 'M-Pesa number registered to you';
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final wallet = context.watch<WalletProvider>();
    final profile = wallet.snapshot?.payoutProfile;
    final restaurant = auth.session?.user.restaurantName ?? wallet.snapshot?.restaurantName;

    if (!_hydratedFromApi && profile != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        setState(() => _hydrateFromSnapshot(profile));
      });
    }

    return WalletPageFrame(
      child: CustomScrollView(
        physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics(),
        ),
        slivers: [
          if (wallet.loadingSummary && wallet.snapshot == null)
            const SliverFillRemaining(
              hasScrollBody: false,
              child: Center(
                child: CircularProgressIndicator(color: AppTheme.primary),
              ),
            )
          else
            SliverPadding(
              padding: WalletLayout.pageInsets(context, top: 4),
              sliver: SliverToBoxAdapter(
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const ScreenTitle(
                        title: 'Payout',
                        subtitle: 'Where we send your withdrawal money',
                      ),
                      const SizedBox(height: 16),
                      PayoutProfileHero(
                        name: auth.session?.user.name ?? 'Manager',
                        email: auth.session?.user.email ?? '',
                        restaurant: restaurant,
                      ),
                      const SizedBox(height: 16),
                      PayoutStatusBanner(
                        isComplete: profile?.isComplete ?? false,
                        method: profile?.payoutMethod,
                        details: profile?.payoutDetails,
                      ),
                      const SizedBox(height: 24),
                      const SectionHeader(title: 'Payout method'),
                      PayoutMethodSelector(
                        selectedMethod: _method,
                        onChanged: (value) => setState(() => _method = value),
                        methodCardBuilder: ({
                          required title,
                          required subtitle,
                          required icon,
                          required selected,
                          required onTap,
                        }) =>
                            PayoutMethodCard(
                          title: title,
                          subtitle: subtitle,
                          icon: icon,
                          selected: selected,
                          onTap: onTap,
                          compact: WalletLayout.compactWidth(context),
                        ),
                      ),
                      const SizedBox(height: 24),
                      const SectionHeader(title: 'Account details'),
                      GlassCard(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            TextFormField(
                              controller: _detailsController,
                              keyboardType: _method == 'Mobile Money'
                                  ? TextInputType.phone
                                  : TextInputType.multiline,
                              maxLines: _method == 'Bank Transfer' ? 4 : 1,
                              decoration: InputDecoration(
                                labelText: _detailsLabel,
                                hintText: _detailsHint,
                                prefixIcon: Icon(
                                  _method == 'Bank Transfer'
                                      ? Icons.account_balance_outlined
                                      : Icons.smartphone_rounded,
                                ),
                              ),
                              validator: (value) =>
                                  (value == null || value.trim().isEmpty)
                                      ? 'Enter your payout details'
                                      : null,
                            ),
                            const SizedBox(height: 16),
                            const _InfoRow(
                              icon: Icons.lock_outline_rounded,
                              text:
                                  'Details are stored securely and used only for withdrawals.',
                            ),
                            const SizedBox(height: 10),
                            const _InfoRow(
                              icon: Icons.flash_on_rounded,
                              text:
                                  'Saved profile speeds up your next withdrawal request.',
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                      DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: AppTheme.brandGradient,
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.primaryDark.withValues(alpha: 0.35),
                              blurRadius: 16,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                          ),
                          onPressed: _saving ? null : _save,
                          child: _saving
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.white,
                                  ),
                                )
                              : const Text('Save payout profile'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: AppTheme.primary),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              color: AppTheme.textMuted,
              fontSize: 12,
              height: 1.45,
            ),
          ),
        ),
      ],
    );
  }
}
