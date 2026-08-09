import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: MaterialApp(
          home: Scaffold(
            body: Text('Rudra PG Resident Portal'),
          ),
        ),
      ),
    );
    expect(find.text('Rudra PG Resident Portal'), findsOneWidget);
  });
}
