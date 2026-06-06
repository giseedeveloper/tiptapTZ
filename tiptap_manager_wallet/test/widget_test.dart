import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:tiptap_manager_wallet/main.dart';
import 'package:tiptap_manager_wallet/providers/app_providers.dart';
import 'package:tiptap_manager_wallet/services/api_service.dart';
import 'package:tiptap_manager_wallet/services/storage_service.dart';

void main() {
  testWidgets('App boots to splash', (WidgetTester tester) async {
    final storage = StorageService();
    final api = ApiService(storage);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => AuthProvider(api, storage)),
          ChangeNotifierProvider(create: (_) => WalletProvider(api)),
        ],
        child: const TiptapManagerWalletApp(),
      ),
    );

    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });
}
