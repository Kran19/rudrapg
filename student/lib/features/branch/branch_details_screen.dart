import 'package:flutter/material.dart';
import '../../data/models/branch_model.dart';
import '../resident/my_room_screen.dart';

class BranchDetailsScreen extends StatelessWidget {
  final BranchModel? branch;
  const BranchDetailsScreen({super.key, this.branch});

  @override
  Widget build(BuildContext context) {
    return const MyRoomScreen();
  }
}
