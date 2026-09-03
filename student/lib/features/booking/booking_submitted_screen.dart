import 'package:flutter/material.dart';
import '../registration_submitted/registration_submitted_screen.dart';

class BookingSubmittedScreen extends StatelessWidget {
  final String appReference;

  const BookingSubmittedScreen({
    super.key,
    this.appReference = 'REG-2026-0001',
  });

  @override
  Widget build(BuildContext context) {
    return RegistrationSubmittedScreen(appReference: appReference);
  }
}
