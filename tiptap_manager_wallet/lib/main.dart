import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'core/config.dart';
import 'providers/app_providers.dart';
import 'providers/settings_provider.dart';
import 'screens/root_gate.dart';
import 'services/api_service.dart';
import 'services/storage_service.dart';
import 'theme/app_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  final storage = StorageService();
  final api = ApiService(storage);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => SettingsProvider(storage)),
        ChangeNotifierProvider(create: (_) => AuthProvider(api, storage)),
        ChangeNotifierProvider(create: (_) => WalletProvider(api)),
      ],
      child: const TiptapManagerWalletApp(),
    ),
  );
}

/// Reads [SettingsProvider] below [MultiProvider] (safe for hot reload).
class TiptapManagerWalletApp extends StatelessWidget {
  const TiptapManagerWalletApp({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = context.watch<SettingsProvider>();

    return MaterialApp(
      title: AppConfig.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.dark,
      themeMode: settings.themeMode,
      locale: settings.locale,
      supportedLocales: const [
        Locale('en'),
        Locale('sw'),
        Locale('en', 'ZA'),
      ],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      home: const RootGate(),
    );
  }
}
