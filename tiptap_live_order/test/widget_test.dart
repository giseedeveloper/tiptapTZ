import 'package:flutter_test/flutter_test.dart';
import 'package:tiptap_live_order/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const TiptapOrderPortal());
    // Just ensure it renders without crashing
    await tester.pump();
  });
}
