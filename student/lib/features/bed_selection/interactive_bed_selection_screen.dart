import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_spacing.dart';
import '../../core/constants/app_typography.dart';
import '../../core/widgets/custom_app_bar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_card.dart';
import '../../data/models/bed_model.dart';
import '../../data/models/room_model.dart';
import '../booking/student_registration_screen.dart';

class InteractiveBedSelectionScreen extends StatefulWidget {
  final RoomModel room;

  const InteractiveBedSelectionScreen({super.key, required this.room});

  @override
  State<InteractiveBedSelectionScreen> createState() => _InteractiveBedSelectionScreenState();
}

class _InteractiveBedSelectionScreenState extends State<InteractiveBedSelectionScreen> {
  BedModel? _selectedBed;

  @override
  Widget build(BuildContext context) {
    final room = widget.room;
    final availableCount = room.availableBedsCount;
    final bookedCount = room.occupiedBedsCount;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: CustomAppBar(title: 'Room ${room.roomNumber} Bed Layout'),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Icon-Based Room Specification Pill Bar (BookMyShow Style)
              Row(
                children: [
                  // Sharing Type Spec Pill
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: AppColors.secondary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.secondary.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(_getSharingIcon(room.sharingType), size: 18, color: AppColors.secondary),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              room.sharingType,
                              style: AppTypography.caption.copyWith(
                                color: AppColors.secondary,
                                fontWeight: FontWeight.bold,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // AC / Non-AC Spec Pill
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: room.isAc
                            ? const Color(0xFF0284C7).withValues(alpha: 0.1)
                            : AppColors.textSecondary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: room.isAc
                              ? const Color(0xFF0284C7).withValues(alpha: 0.3)
                              : AppColors.divider,
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            room.isAc ? Icons.ac_unit_rounded : Icons.air_rounded,
                            size: 18,
                            color: room.isAc ? const Color(0xFF0284C7) : AppColors.textSecondary,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            room.isAc ? 'AC Room' : 'Non-AC',
                            style: AppTypography.caption.copyWith(
                              color: room.isAc ? const Color(0xFF0284C7) : AppColors.textSecondary,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Booked vs Available Counter Spec Pill
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: availableCount > 0
                            ? AppColors.success.withValues(alpha: 0.1)
                            : AppColors.error.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: availableCount > 0
                              ? AppColors.success.withValues(alpha: 0.3)
                              : AppColors.error.withValues(alpha: 0.3),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            availableCount > 0 ? Icons.check_circle_outline_rounded : Icons.block_rounded,
                            size: 18,
                            color: availableCount > 0 ? AppColors.success : AppColors.error,
                          ),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              bookedCount > 0 ? '$bookedCount Booked • $availableCount Left' : '$availableCount Left',
                              style: AppTypography.caption.copyWith(
                                color: availableCount > 0 ? AppColors.success : AppColors.error,
                                fontWeight: FontWeight.bold,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),

              // Status Legend Bar
              CustomCard(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildLegendItem('Available', AppColors.bedAvailable, Icons.king_bed_rounded),
                    _buildLegendItem('Selected', AppColors.bedSelected, Icons.check_circle_rounded),
                    _buildLegendItem('Reserved', AppColors.bedReserved, Icons.lock_clock_rounded),
                    _buildLegendItem('Booked', AppColors.bedOccupied, Icons.person_rounded),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // 2D Visual Room Floorplan Grid Container (BookMyShow Screen Layout Style)
              Expanded(
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(AppSpacing.radiusCard),
                    border: Border.all(color: AppColors.divider, width: 2),
                    boxShadow: AppSpacing.cardShadow,
                  ),
                  child: Column(
                    children: [
                      // Room Entrance Door Visual Indicator
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.08),
                              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(10)),
                              border: Border.all(color: AppColors.divider),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.meeting_room_rounded, size: 16, color: AppColors.primary),
                                const SizedBox(width: 6),
                                Text(
                                  'ROOM ENTRANCE DOOR',
                                  style: AppTypography.caption.copyWith(
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1.0,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.xl),

                      // Interactive Bed Selection Grid
                      Expanded(
                        child: GridView.builder(
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            crossAxisSpacing: 16,
                            mainAxisSpacing: 16,
                            childAspectRatio: 0.85,
                          ),
                          itemCount: room.beds.length,
                          itemBuilder: (context, index) {
                            final bed = room.beds[index];
                            final isSelected = _selectedBed?.id == bed.id;
                            return _buildBookMyShowBedWidget(bed: bed, isSelected: isSelected);
                          },
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // Bottom Selection Summary Card
              if (_selectedBed != null) ...[
                CustomCard(
                  backgroundColor: AppColors.secondary.withValues(alpha: 0.08),
                  border: Border.all(color: AppColors.secondary),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: const BoxDecoration(
                              color: AppColors.secondary,
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.king_bed_rounded, color: Colors.white, size: 20),
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('SELECTED SPOT', style: AppTypography.badge.copyWith(color: AppColors.secondary)),
                              Text('${_selectedBed!.code} (Room ${room.roomNumber})', style: AppTypography.titleMedium),
                            ],
                          ),
                        ],
                      ),
                      Text('₹${room.monthlyRent.toInt()}/mo', style: AppTypography.titleLarge.copyWith(color: AppColors.secondary)),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.md),
              ],

              // Primary CTA Button
              CustomButton(
                text: _selectedBed != null ? 'Proceed with ${_selectedBed!.code}' : 'Tap an Available Green Bed',
                type: _selectedBed != null ? CustomButtonType.secondary : CustomButtonType.primary,
                icon: _selectedBed != null ? Icons.arrow_forward_rounded : Icons.touch_app_rounded,
                onPressed: _selectedBed != null
                    ? () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => StudentRegistrationScreen(
                              room: room,
                              selectedBed: _selectedBed!,
                            ),
                          ),
                        );
                      }
                    : () {},
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getSharingIcon(String sharingType) {
    final lower = sharingType.toLowerCase();
    if (lower.contains('private') || lower.contains('1') || lower.contains('solo')) {
      return Icons.person_rounded;
    } else if (lower.contains('2')) {
      return Icons.people_rounded;
    } else if (lower.contains('3')) {
      return Icons.groups_rounded;
    } else {
      return Icons.group_work_rounded;
    }
  }

  Widget _buildLegendItem(String label, Color color, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          child: Icon(icon, size: 10, color: Colors.white),
        ),
        const SizedBox(width: 4),
        Text(label, style: AppTypography.caption.copyWith(fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _buildBookMyShowBedWidget({required BedModel bed, required bool isSelected}) {
    Color cardBg;
    Color strokeColor;
    String statusText;
    IconData statusIcon;
    bool isClickable = false;

    if (isSelected) {
      cardBg = AppColors.bedSelected.withValues(alpha: 0.15);
      strokeColor = AppColors.bedSelected;
      statusText = 'SELECTED';
      statusIcon = Icons.check_circle_rounded;
      isClickable = true;
    } else {
      switch (bed.status) {
        case BedStatus.available:
          cardBg = AppColors.bedAvailable.withValues(alpha: 0.08);
          strokeColor = AppColors.bedAvailable;
          statusText = 'AVAILABLE';
          statusIcon = Icons.king_bed_rounded;
          isClickable = true;
          break;
        case BedStatus.occupied:
          cardBg = AppColors.bedOccupied.withValues(alpha: 0.05);
          strokeColor = AppColors.bedOccupied.withValues(alpha: 0.3);
          statusText = 'BOOKED';
          statusIcon = Icons.lock_rounded;
          break;
        case BedStatus.reserved:
          cardBg = AppColors.bedReserved.withValues(alpha: 0.08);
          strokeColor = AppColors.bedReserved;
          statusText = 'RESERVED';
          statusIcon = Icons.lock_clock_rounded;
          break;
        case BedStatus.selected:
          cardBg = AppColors.bedSelected.withValues(alpha: 0.15);
          strokeColor = AppColors.bedSelected;
          statusText = 'SELECTED';
          statusIcon = Icons.check_circle_rounded;
          break;
      }
    }

    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      curve: Curves.easeInOut,
      child: GestureDetector(
        onTap: isClickable
            ? () {
                setState(() {
                  _selectedBed = bed;
                });
              }
            : null,
        child: Container(
          decoration: BoxDecoration(
            color: cardBg,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: strokeColor, width: isSelected ? 3.0 : 1.5),
            boxShadow: isSelected ? AppSpacing.softShadow : null,
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Visual Bed Frame with Pillow Graphic
              Container(
                width: 54,
                height: 30,
                decoration: BoxDecoration(
                  color: strokeColor.withValues(alpha: 0.3),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: strokeColor.withValues(alpha: 0.6)),
                ),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    // Pillow
                    Positioned(
                      top: 4,
                      child: Container(
                        width: 24,
                        height: 8,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                    // Bed Mattress Fold
                    Positioned(
                      bottom: 4,
                      child: Container(
                        width: 44,
                        height: 10,
                        decoration: BoxDecoration(
                          color: strokeColor.withValues(alpha: 0.4),
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),

              // Bed Code Title
              Text(
                bed.code,
                style: AppTypography.titleMedium.copyWith(
                  color: isSelected ? AppColors.secondary : AppColors.textPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),

              // Status Icon Pill
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: strokeColor,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, size: 11, color: Colors.white),
                    const SizedBox(width: 3),
                    Text(
                      statusText,
                      style: AppTypography.badge.copyWith(color: Colors.white, fontSize: 10),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
