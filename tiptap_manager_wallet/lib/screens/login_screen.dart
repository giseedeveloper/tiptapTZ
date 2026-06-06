import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/config.dart';
import '../providers/app_providers.dart';
import '../theme/app_theme.dart';
import '../widgets/tiptap_logo.dart';
import '../widgets/wallet_widgets.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _loading = false;
  bool _obscure = true;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    FocusManager.instance.primaryFocus?.unfocus();

    final auth = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.maybeOf(context);

    setState(() => _loading = true);

    final ok = await auth.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (ok) {
      // RootGate swaps to HomeShell — do not touch this widget's state/context.
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() => _loading = false);

    messenger?.showSnackBar(
      SnackBar(content: Text(auth.error ?? 'Login failed.')),
    );
  }

  @override
  void dispose() {
    FocusManager.instance.primaryFocus?.unfocus();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return PortalBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 12),
                  Center(child: TiptapLogo(size: isDark ? 96 : 104)),
                  const SizedBox(height: 24),
                  Text(
                    AppConfig.appName,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.5,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Manager wallet — balance, payments & withdrawals in one secure place.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppTheme.textSecondaryOf(context),
                      height: 1.55,
                      fontSize: 15,
                    ),
                  ),
                  const SizedBox(height: 32),
                  GlassCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 14,
                            vertical: 10,
                          ),
                          decoration: BoxDecoration(
                            gradient: AppTheme.brandGradient,
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                Icons.account_balance_wallet_rounded,
                                color: Colors.white.withValues(alpha: 0.92),
                                size: 20,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  'Sign in with your manager account',
                                  style: TextStyle(
                                    color: Colors.white.withValues(alpha: 0.95),
                                    fontWeight: FontWeight.w700,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 22),
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          autofillHints: const [AutofillHints.email],
                          decoration: const InputDecoration(
                            labelText: 'Email',
                            prefixIcon: Icon(Icons.email_outlined),
                          ),
                          validator: (v) =>
                              (v == null || v.trim().isEmpty) ? 'Email is required' : null,
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscure,
                          autofillHints: const [AutofillHints.password],
                          decoration: InputDecoration(
                            labelText: 'Password',
                            prefixIcon: const Icon(Icons.lock_outline),
                            suffixIcon: IconButton(
                              onPressed: () => setState(() => _obscure = !_obscure),
                              icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility),
                            ),
                          ),
                          validator: (v) =>
                              (v == null || v.isEmpty) ? 'Password is required' : null,
                        ),
                        const SizedBox(height: 24),
                        DecoratedBox(
                          decoration: BoxDecoration(
                            gradient: AppTheme.brandGradient,
                            borderRadius: BorderRadius.circular(18),
                            boxShadow: [
                              BoxShadow(
                                color: AppTheme.primaryDark.withValues(alpha: 0.32),
                                blurRadius: 18,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.transparent,
                              shadowColor: Colors.transparent,
                            ),
                            onPressed: _loading ? null : _submit,
                            child: _loading
                                ? const SizedBox(
                                    width: 22,
                                    height: 22,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Text('Sign in'),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text(
                    'Protected by TIPTAP · ${AppConfig.apiBaseUrl.contains('.co.za') ? 'South Africa' : 'Tanzania'}',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppTheme.textMutedOf(context),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
