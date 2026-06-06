import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_providers.dart';
import '../providers/settings_provider.dart';
import 'home_shell.dart';
import 'login_screen.dart';
import 'splash_screen.dart';

/// Single app root — avoids duplicate routes / GlobalKeys on hot reload.
class RootGate extends StatefulWidget {
  const RootGate({super.key});

  @override
  State<RootGate> createState() => _RootGateState();
}

class _RootGateState extends State<RootGate> {
  bool _splashMinElapsed = false;
  bool _bootStarted = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _boot());
  }

  Future<void> _boot() async {
    if (_bootStarted) {
      return;
    }
    _bootStarted = true;

    final auth = context.read<AuthProvider>();
    final settings = context.read<SettingsProvider>();

    await Future.wait([
      auth.bootstrap(),
      settings.bootstrap(),
      Future<void>.delayed(const Duration(milliseconds: 2200)),
    ]);

    if (!mounted) {
      return;
    }

    setState(() => _splashMinElapsed = true);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final settings = context.watch<SettingsProvider>();

    if (!_splashMinElapsed || auth.bootstrapping || !settings.ready) {
      return const SplashScreen(key: ValueKey('splash'));
    }

    if (auth.isAuthenticated) {
      return const HomeShell(key: ValueKey('home'));
    }

    return const LoginScreen(key: ValueKey('login'));
  }
}
