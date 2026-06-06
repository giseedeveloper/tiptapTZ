import 'package:flutter/material.dart';

import '../widgets/wallet_widgets.dart';
import 'activity_screen.dart';
import 'payout_profile_screen.dart';
import 'settings_screen.dart';
import 'wallet_home_screen.dart';

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;

  void _openActivity() => setState(() => _index = 1);

  @override
  Widget build(BuildContext context) {
    final pages = [
      WalletHomeScreen(key: const ValueKey('wallet'), onOpenActivity: _openActivity),
      const ActivityScreen(key: ValueKey('activity')),
      const PayoutProfileScreen(key: ValueKey('payout')),
      const SettingsScreen(key: ValueKey('settings')),
    ];

    return PortalBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        extendBody: true,
        body: IndexedStack(index: _index, children: pages),
        bottomNavigationBar: FloatingNavBar(
          index: _index,
          onChanged: (value) => setState(() => _index = value),
        ),
      ),
    );
  }
}
