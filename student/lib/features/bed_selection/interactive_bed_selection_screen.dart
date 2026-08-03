import 'package:flutter/material.dart';
import '../../data/models/room_model.dart';
import '../resident/my_room_screen.dart';

class InteractiveBedSelectionScreen extends StatelessWidget {
  final RoomModel? room;
  const InteractiveBedSelectionScreen({super.key, this.room});

  @override
  Widget build(BuildContext context) {
    return const MyRoomScreen();
  }
}
