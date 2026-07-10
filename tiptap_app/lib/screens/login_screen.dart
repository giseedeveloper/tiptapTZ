import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../services/storage_service.dart';
import '../widgets/auth_email_divider.dart';
import '../widgets/auth_social_section.dart';
import '../widgets/auth_theme.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _storage = StorageService();
  bool _obscurePassword = true;
  bool _rememberMe = false;

  late AnimationController _shakeController;
  late Animation<double> _shakeAnimation;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
    _shakeAnimation = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(parent: _shakeController, curve: Curves.easeInOut),
    );
    _loadSavedPrefs();
  }

  Future<void> _loadSavedPrefs() async {
    final remembered = await _storage.getRememberMe();
    final savedEmail = await _storage.getSavedEmail();
    if (mounted) {
      setState(() {
        _rememberMe = remembered;
        if (savedEmail != null) _emailController.text = savedEmail;
      });
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _shakeController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final ok = await auth.login(
      _emailController.text.trim(),
      _passwordController.text,
      rememberMe: _rememberMe,
    );
    if (!ok && mounted) {
      _shakeController.forward(from: 0);
      await Future.delayed(const Duration(milliseconds: 100));
      if (mounted) _shakeController.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final compact = MediaQuery.of(context).size.height < 780;
    final isLoading = context.watch<AuthProvider>().isLoading;
    final error = context.watch<AuthProvider>().error;

    return AuthPageShell(
      footer: Text(
        '© ${DateTime.now().year} TIPTAP. All rights reserved.',
        style: AuthTheme.subtitleStyle(size: 11),
        textAlign: TextAlign.center,
      ),
      child: AnimatedBuilder(
        animation: _shakeAnimation,
        builder: (context, child) {
          final shake = math.sin(_shakeAnimation.value * math.pi * 6) * 8;
          return Transform.translate(offset: Offset(shake, 0), child: child);
        },
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
                SizedBox(height: compact ? 14 : 18),
                AuthSocialSection(
                  intent: 'login',
                  role: 'waiter',
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
                    if (v == null || v.trim().isEmpty) return 'Enter email';
                    if (!v.contains('@')) return 'Invalid email';
                    return null;
                  },
                ),
                SizedBox(height: compact ? 10 : 12),
                AuthTextField(
                  controller: _passwordController,
                  label: 'Password',
                  hint: '••••••••',
                  icon: Icons.lock_outline_rounded,
                  obscure: _obscurePassword,
                  compact: compact,
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword
                          ? Icons.visibility_off_rounded
                          : Icons.visibility_rounded,
                      color: AuthTheme.textSecondary.withValues(alpha: 0.55),
                      size: 18,
                    ),
                    onPressed: () =>
                        setState(() => _obscurePassword = !_obscurePassword),
                  ),
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Enter password';
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
                        style: GoogleFonts.poppins(
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                          color: AuthTheme.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                if (error != null) ...[
                  const SizedBox(height: 12),
                  AuthTheme.errorBanner(error),
                ],
                SizedBox(height: compact ? 14 : 16),
                AuthTheme.primaryButton(
                  label: 'Sign in',
                  onPressed: isLoading ? null : _submit,
                  loading: isLoading,
                  compact: compact,
                ),
                SizedBox(height: compact ? 14 : 16),
                GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const RegisterScreen(),
                      ),
                    );
                  },
                  child: Text.rich(
                    TextSpan(
                      text: 'Don\'t have an account? ',
                      style: AuthTheme.subtitleStyle(size: 12),
                      children: [
                        TextSpan(
                          text: 'Register waiter',
                          style: AuthTheme.linkStyle(size: 12),
                        ),
                      ],
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
