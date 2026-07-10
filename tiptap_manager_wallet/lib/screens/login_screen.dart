import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/config.dart';
import '../providers/app_providers.dart';
import '../widgets/auth_email_divider.dart';
import '../widgets/auth_social_section.dart';
import '../widgets/auth_theme.dart';

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
  bool _rememberMe = false;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    FocusManager.instance.primaryFocus?.unfocus();
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);

    final ok = await auth.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (ok) return;

    if (!mounted) return;
    setState(() => _loading = false);

    final error = auth.error ?? 'Login failed.';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(error),
        backgroundColor: AuthTheme.purpleDark,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final compact = MediaQuery.of(context).size.height < 780;
    final market = AppConfig.apiBaseUrl.contains('.co.za')
        ? 'South Africa'
        : 'Tanzania';

    return AuthPageShell(
      footer: Text(
        'Protected by TIPTAP · $market\n© ${DateTime.now().year} TIPTAP. All rights reserved.',
        style: AuthTheme.subtitleStyle(size: 11),
        textAlign: TextAlign.center,
      ),
      child: AuthGlassCard(
        padding: EdgeInsets.all(compact ? 18 : 22),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'Welcome back!',
                textAlign: TextAlign.center,
                style: AuthTheme.titleStyle(size: compact ? 22 : 24),
              ),
              const SizedBox(height: 6),
              Text(
                'Sign in to your TIPTAP account',
                textAlign: TextAlign.center,
                style: AuthTheme.subtitleStyle(size: compact ? 12 : 13),
              ),
              const SizedBox(height: 4),
              Text(
                'Manager wallet — balance, payments & withdrawals.',
                textAlign: TextAlign.center,
                style: AuthTheme.subtitleStyle(size: 11),
              ),
              SizedBox(height: compact ? 14 : 18),
              AuthSocialSection(
                intent: 'login',
                role: 'manager',
                compact: compact,
              ),
              const AuthEmailDivider(),
              AuthTextField(
                controller: _emailController,
                label: 'Email',
                hint: 'you@example.com',
                icon: Icons.alternate_email_rounded,
                keyboardType: TextInputType.emailAddress,
                compact: compact,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Email is required';
                  return null;
                },
              ),
              SizedBox(height: compact ? 10 : 12),
              AuthTextField(
                controller: _passwordController,
                label: 'Password',
                hint: '••••••••',
                icon: Icons.lock_outline_rounded,
                obscure: _obscure,
                compact: compact,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscure
                        ? Icons.visibility_off_rounded
                        : Icons.visibility_rounded,
                    color: AuthTheme.textSecondary.withValues(alpha: 0.55),
                    size: 18,
                  ),
                  onPressed: () => setState(() => _obscure = !_obscure),
                ),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Password is required';
                  return null;
                },
              ),
              SizedBox(height: compact ? 10 : 12),
              GestureDetector(
                onTap: () => setState(() => _rememberMe = !_rememberMe),
                child: Row(
                  children: [
                    SizedBox(
                      width: 20,
                      height: 20,
                      child: Checkbox(
                        value: _rememberMe,
                        onChanged: (v) =>
                            setState(() => _rememberMe = v ?? false),
                        activeColor: AuthTheme.purpleDark,
                        side: const BorderSide(color: AuthTheme.lavenderSoft),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Remember me',
                      style: AuthTheme.subtitleStyle(size: 13),
                    ),
                  ],
                ),
              ),
              SizedBox(height: compact ? 14 : 16),
              AuthTheme.primaryButton(
                label: 'Sign in',
                onPressed: _loading ? null : _submit,
                loading: _loading,
                compact: compact,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
