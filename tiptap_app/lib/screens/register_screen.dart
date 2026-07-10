import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../widgets/auth_email_divider.dart';
import '../widgets/auth_social_section.dart';
import '../widgets/auth_theme.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _locationController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  int _currentStep = 0;

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
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _locationController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _shakeController.dispose();
    super.dispose();
  }

  void _nextStep() {
    if (_currentStep != 0) return;
    if (!_formKey.currentState!.validate()) return;
    setState(() => _currentStep = 1);
  }

  void _previousStep() {
    if (_currentStep != 1) return;
    setState(() => _currentStep = 0);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final ok = await auth.register(
      firstName: _firstNameController.text.trim(),
      lastName: _lastNameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      location: _locationController.text.trim().isEmpty
          ? null
          : _locationController.text.trim(),
      password: _passwordController.text,
      passwordConfirmation: _confirmPasswordController.text,
    );
    if (ok && mounted) {
      _showSuccessDialog();
    } else if (!ok && mounted) {
      _shakeController.forward(from: 0);
      await Future.delayed(const Duration(milliseconds: 100));
      if (mounted) _shakeController.reverse();
    }
  }

  void _showSuccessDialog() {
    final auth = context.read<AuthProvider>();
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        child: AuthGlassCard(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: AuthTheme.heroGradient,
                ),
                child: const Icon(Icons.check_rounded, color: Colors.white, size: 36),
              ),
              const SizedBox(height: 16),
              Text('Account created', style: AuthTheme.titleStyle(size: 20)),
              const SizedBox(height: 8),
              Text(
                auth.registrationMessage ??
                    'You\'ll receive a unique waiter number instantly. A restaurant manager will link you to their team.',
                textAlign: TextAlign.center,
                style: AuthTheme.subtitleStyle(size: 12),
              ),
              if (auth.user?.globalWaiterNumber != null) ...[
                const SizedBox(height: 14),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AuthTheme.lavender,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AuthTheme.lavenderSoft),
                  ),
                  child: Column(
                    children: [
                      Text(
                        'Your unique number',
                        style: AuthTheme.subtitleStyle(size: 11),
                      ),
                      Text(
                        auth.user!.globalWaiterNumber!,
                        style: GoogleFonts.poppins(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: AuthTheme.purpleDark,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 18),
              AuthTheme.primaryButton(
                label: 'Got it',
                onPressed: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).pop();
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _passwordToggle(bool obscure, VoidCallback onTap) {
    return IconButton(
      icon: Icon(
        obscure ? Icons.visibility_off_rounded : Icons.visibility_rounded,
        color: AuthTheme.textSecondary.withValues(alpha: 0.55),
        size: 18,
      ),
      onPressed: onTap,
    );
  }

  @override
  Widget build(BuildContext context) {
    final compact = MediaQuery.of(context).size.height < 780;
    final isLoading = context.watch<AuthProvider>().isLoading;
    final error = context.watch<AuthProvider>().error;

    return AuthPageShell(
      showBrandHeader: _currentStep == 0,
      leading: IconButton(
        onPressed: () {
          if (_currentStep == 1) {
            _previousStep();
          } else {
            Navigator.pop(context);
          }
        },
        icon: const Icon(Icons.arrow_back_rounded, color: AuthTheme.textPrimary),
      ),
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
                if (_currentStep == 0) ...[
                  Text(
                    'Register as Waiter',
                    textAlign: TextAlign.center,
                    style: AuthTheme.titleStyle(size: compact ? 22 : 24),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Get your unique number — a manager will link you to a restaurant',
                    textAlign: TextAlign.center,
                    style: AuthTheme.subtitleStyle(size: compact ? 12 : 13),
                  ),
                  SizedBox(height: compact ? 14 : 18),
                  AuthSocialSection(
                    intent: 'register',
                    role: 'waiter',
                    compact: compact,
                  ),
                  const AuthEmailDivider(label: 'Or register with email'),
                ] else ...[
                  Text(
                    'Step 2 of 2',
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AuthTheme.purpleDark,
                    ),
                  ),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(999),
                    child: const LinearProgressIndicator(
                      value: 1,
                      minHeight: 6,
                      backgroundColor: AuthTheme.lavender,
                      valueColor: AlwaysStoppedAnimation<Color>(AuthTheme.purple),
                    ),
                  ),
                  SizedBox(height: compact ? 14 : 18),
                  Text(
                    'Your profile',
                    textAlign: TextAlign.center,
                    style: AuthTheme.titleStyle(size: compact ? 20 : 22),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Tell us a bit about yourself',
                    textAlign: TextAlign.center,
                    style: AuthTheme.subtitleStyle(size: compact ? 12 : 13),
                  ),
                  SizedBox(height: compact ? 12 : 16),
                  _buildEmailBadge(),
                  SizedBox(height: compact ? 12 : 16),
                ],
                if (_currentStep == 0) ..._credentialsFields(compact) else ..._profileFields(compact),
                if (error != null) ...[
                  const SizedBox(height: 12),
                  AuthTheme.errorBanner(error),
                ],
                SizedBox(height: compact ? 14 : 16),
                if (_currentStep == 0)
                  AuthTheme.primaryButton(
                    label: 'Continue',
                    onPressed: _nextStep,
                    compact: compact,
                  )
                else
                  Row(
                    children: [
                      TextButton(
                        onPressed: isLoading ? null : _previousStep,
                        child: Text('Back', style: AuthTheme.linkStyle()),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: AuthTheme.primaryButton(
                          label: 'Create account',
                          onPressed: isLoading ? null : _submit,
                          loading: isLoading,
                          compact: compact,
                          expanded: false,
                        ),
                      ),
                    ],
                  ),
                if (_currentStep == 0) ...[
                  SizedBox(height: compact ? 14 : 16),
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: Text.rich(
                      TextSpan(
                        text: 'Already have an account? ',
                        style: AuthTheme.subtitleStyle(size: 12),
                        children: [
                          TextSpan(
                            text: 'Sign in here',
                            style: AuthTheme.linkStyle(size: 12),
                          ),
                        ],
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmailBadge() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AuthTheme.border),
        boxShadow: [
          BoxShadow(
            color: AuthTheme.textPrimary.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: AuthTheme.lavender,
              border: Border.all(color: AuthTheme.lavenderSoft),
            ),
            child: const Icon(
              Icons.alternate_email_rounded,
              color: AuthTheme.purpleDark,
              size: 18,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Signing up as', style: AuthTheme.subtitleStyle(size: 11)),
                Text(
                  _emailController.text.trim(),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.poppins(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AuthTheme.textPrimary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  List<Widget> _credentialsFields(bool compact) {
    return [
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
        hint: 'At least 8 characters',
        icon: Icons.lock_outline_rounded,
        obscure: _obscurePassword,
        compact: compact,
        suffixIcon: _passwordToggle(
          _obscurePassword,
          () => setState(() => _obscurePassword = !_obscurePassword),
        ),
        validator: (v) {
          if (v == null || v.isEmpty) return 'Enter password';
          if (v.length < 8) return 'At least 8 characters';
          return null;
        },
      ),
      SizedBox(height: compact ? 10 : 12),
      AuthTextField(
        controller: _confirmPasswordController,
        label: 'Confirm password',
        hint: 'Repeat password',
        icon: Icons.verified_user_outlined,
        obscure: _obscureConfirmPassword,
        compact: compact,
        suffixIcon: _passwordToggle(
          _obscureConfirmPassword,
          () => setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
        ),
        validator: (v) {
          if (v == null || v.isEmpty) return 'Confirm password';
          if (v != _passwordController.text) return 'Passwords do not match';
          return null;
        },
      ),
    ];
  }

  List<Widget> _profileFields(bool compact) {
    return [
      Row(
        children: [
          Expanded(
            child: AuthTextField(
              controller: _firstNameController,
              label: 'First name',
              hint: 'First name',
              icon: Icons.person_outline_rounded,
              compact: compact,
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: AuthTextField(
              controller: _lastNameController,
              label: 'Last name',
              hint: 'Last name',
              icon: Icons.person_outline_rounded,
              compact: compact,
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
          ),
        ],
      ),
      SizedBox(height: compact ? 10 : 12),
      Row(
        children: [
          Expanded(
            child: AuthTextField(
              controller: _phoneController,
              label: 'Phone',
              hint: 'e.g. 071 234 5678',
              icon: Icons.phone_outlined,
              keyboardType: TextInputType.phone,
              compact: compact,
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Enter phone' : null,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: AuthTextField(
              controller: _locationController,
              label: 'City (optional)',
              hint: 'e.g. Dar es Salaam',
              icon: Icons.location_on_outlined,
              compact: compact,
            ),
          ),
        ],
      ),
      const SizedBox(height: 12),
      Text(
        'You\'ll receive a unique waiter number instantly. A restaurant manager will link you to their team.',
        style: AuthTheme.subtitleStyle(size: 11),
      ),
    ];
  }
}
