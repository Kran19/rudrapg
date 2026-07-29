import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/room_card.dart';
import '../../data/dummy/dummy_data.dart';
import '../../data/models/bed_model.dart';
import '../../data/models/room_model.dart';
import '../bed_selection/interactive_bed_selection_screen.dart';
import 'room_details_screen.dart';

class RoomListingScreen extends StatefulWidget {
  const RoomListingScreen({super.key});

  @override
  State<RoomListingScreen> createState() => _RoomListingScreenState();
}

class _RoomListingScreenState extends State<RoomListingScreen> {
  String _selectedFilter = 'All';
  bool _isAcOnly = false;
  bool _isGridView = true; // Default to 40-Room Master Visual Chart view!

  final List<String> _filters = ['All', '2 Sharing', '3 Sharing', 'Private Room', '4 Sharing'];

  @override
  Widget build(BuildContext context) {
    final rooms = DummyData.sampleRooms.where((room) {
      if (_isAcOnly && !room.isAc) return false;
      if (_selectedFilter == 'All') return true;
      return room.sharingType.toLowerCase().contains(_selectedFilter.toLowerCase());
    }).toList();

    // Group rooms by floor (Floor 1, 2, 3, 4)
    final Map<int, List<RoomModel>> roomsByFloor = {};
    for (var r in rooms) {
      roomsByFloor.putIfAbsent(r.floor, () => []).add(r);
    }

    final availableBeds = rooms.fold<int>(0, (sum, r) => sum + r.availableBedsCount);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: CustomAppBar(
        title: 'Branch Room Occupancy',
        actions: [
          // AC Only Toggle Icon
          IconButton(
            icon: Icon(
              Icons.ac_unit_rounded,
              color: _isAcOnly ? const Color(0xFF0284C7) : AppColors.textMuted,
            ),
            tooltip: 'AC Rooms Only',
            onPressed: () {
              setState(() {
                _isAcOnly = !_isAcOnly;
              });
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Top View Mode Toggle: [ 📊 40-Room Master Chart Matrix ] vs [ 📋 Detailed Cards ]
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.divider),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _isGridView = true),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: _isGridView ? AppColors.primary : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.grid_view_rounded,
                                    size: 16,
                                    color: _isGridView ? Colors.white : AppColors.textSecondary,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '40-Room Master Chart',
                                    style: AppTypography.caption.copyWith(
                                      color: _isGridView ? Colors.white : AppColors.textSecondary,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _isGridView = false),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: !_isGridView ? AppColors.primary : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.view_agenda_rounded,
                                    size: 16,
                                    color: !_isGridView ? Colors.white : AppColors.textSecondary,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Detailed Cards',
                                    style: AppTypography.caption.copyWith(
                                      color: !_isGridView ? Colors.white : AppColors.textSecondary,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Filter Chips Row
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
            child: Row(
              children: _filters.map((filter) {
                final isSelected = _selectedFilter == filter;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(filter),
                    selected: isSelected,
                    onSelected: (val) {
                      setState(() {
                        _selectedFilter = filter;
                      });
                    },
                    selectedColor: AppColors.secondary,
                    backgroundColor: Colors.white,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : AppColors.textPrimary,
                      fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
                      fontSize: 12,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(
                        color: isSelected ? AppColors.secondary : AppColors.divider,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),

          // Overview Legend & Live Occupancy Metrics
          Container(
            margin: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 4),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.divider),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Text(
                      '${rooms.length} Rooms',
                      style: AppTypography.caption.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.success.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '🟢 $availableBeds Beds Left',
                        style: AppTypography.caption.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
                // Legend
                Row(
                  children: [
                    _buildLegendDot(AppColors.bedOccupied, 'Occupied 🔴'),
                    const SizedBox(width: 8),
                    _buildLegendDot(AppColors.bedAvailable, 'Available 🟢'),
                  ],
                ),
              ],
            ),
          ),

          // Main View Content: 40-Room Chart Grid OR Detailed List View
          Expanded(
            child: rooms.isEmpty
                ? Center(
                    child: Text(
                      'No rooms match selected filter.',
                      style: AppTypography.bodyMedium,
                    ),
                  )
                : _isGridView
                    ? _buildMaster40RoomChartGrid(roomsByFloor)
                    : ListView.separated(
                        padding: const EdgeInsets.all(AppSpacing.lg),
                        itemCount: rooms.length,
                        separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.lg),
                        itemBuilder: (context, index) {
                          final room = rooms[index];
                          return RoomCard(
                            room: room,
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => RoomDetailsScreen(room: room),
                                ),
                              );
                            },
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildLegendDot(Color color, String label) {
    return Row(
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 4),
        Text(label, style: AppTypography.caption.copyWith(fontSize: 10)),
      ],
    );
  }

  // MASTER 40-ROOM VISUAL MATRIX CHART (5 ROOMS PER ROW x 8 ROWS ACROSS 4 FLOORS)
  Widget _buildMaster40RoomChartGrid(Map<int, List<RoomModel>> roomsByFloor) {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
      itemCount: roomsByFloor.keys.length,
      itemBuilder: (context, index) {
        final floorNum = roomsByFloor.keys.elementAt(index);
        final floorRooms = roomsByFloor[floorNum]!;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Floor Header Divider Bar
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      'FLOOR $floorNum',
                      style: AppTypography.badge.copyWith(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(child: Container(height: 1, color: AppColors.divider)),
                  const SizedBox(width: 8),
                  Text(
                    '${floorRooms.where((r) => r.availableBedsCount > 0).length}/${floorRooms.length} Available',
                    style: AppTypography.caption.copyWith(color: AppColors.textSecondary),
                  ),
                ],
              ),
            ),

            // 5-Column Room Grid (5 Rooms per row = 2 lines per floor = 8 lines total for 40 rooms!)
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 5, // Exactly 5 rooms per row as requested!
                crossAxisSpacing: 8,
                mainAxisSpacing: 8,
                childAspectRatio: 0.82,
              ),
              itemCount: floorRooms.length,
              itemBuilder: (context, rIndex) {
                final room = floorRooms[rIndex];
                return _buildCompactMasterRoomTile(room);
              },
            ),
            const SizedBox(height: 12),
          ],
        );
      },
    );
  }

  // COMPACT MASTER ROOM TILE (SHOWING EXACT PEOPLE / BED SYMBOLS)
  Widget _buildCompactMasterRoomTile(RoomModel room) {
    final available = room.availableBedsCount;
    final isFull = available == 0;

    final borderColor = isFull ? AppColors.bedOccupied : AppColors.bedAvailable;
    final bgColor = isFull
        ? AppColors.bedOccupied.withValues(alpha: 0.06)
        : AppColors.bedAvailable.withValues(alpha: 0.08);

    return InkWell(
      onTap: () {
        // Open Bed Selection Screen for this room
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => InteractiveBedSelectionScreen(room: room),
          ),
        );
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.all(6),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: borderColor.withValues(alpha: 0.8), width: 1.5),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Top Row: Room Number & AC Icon
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  room.roomNumber,
                  style: AppTypography.caption.copyWith(
                    fontWeight: FontWeight.bold,
                    fontSize: 11,
                    color: AppColors.primary,
                  ),
                ),
                if (room.isAc)
                  const Icon(Icons.ac_unit_rounded, size: 10, color: Color(0xFF0284C7))
                else
                  const Icon(Icons.air_rounded, size: 10, color: AppColors.textMuted),
              ],
            ),

            // Middle Row: EXACT PEOPLE / BED SYMBOLS (Dark/Solid for Occupied, Light/Outline Green for Available)
            Wrap(
              alignment: WrapAlignment.center,
              spacing: 2,
              runSpacing: 2,
              children: room.beds.map((bed) {
                return _buildPersonSymbolIcon(bed.status);
              }).toList(),
            ),

            // Bottom Availability Tag
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
              decoration: BoxDecoration(
                color: borderColor,
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                isFull ? 'FULL' : '$available Left',
                style: AppTypography.badge.copyWith(
                  color: Colors.white,
                  fontSize: 9,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // INDIVIDUAL PERSON SYMBOL WIDGET
  Widget _buildPersonSymbolIcon(BedStatus status) {
    switch (status) {
      case BedStatus.occupied:
        // Dark / Solid Filled Red Person (Booked)
        return const Icon(
          Icons.person_rounded,
          size: 14,
          color: AppColors.bedOccupied,
        );
      case BedStatus.available:
        // Light / Outline Green Person (Available Left)
        return Container(
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.bedAvailable, width: 1.2),
          ),
          child: const Icon(
            Icons.person_outline_rounded,
            size: 11,
            color: AppColors.bedAvailable,
          ),
        );
      case BedStatus.reserved:
        // Amber Person (Reserved)
        return const Icon(
          Icons.person_rounded,
          size: 14,
          color: AppColors.bedReserved,
        );
      case BedStatus.selected:
        return const Icon(
          Icons.person_rounded,
          size: 14,
          color: AppColors.bedSelected,
        );
    }
  }
}
