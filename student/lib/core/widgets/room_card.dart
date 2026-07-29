import 'package:flutter/material.dart';
import '../../data/models/bed_model.dart';
import '../../data/models/room_model.dart';
import '../constants/app_colors.dart';
import '../constants/app_spacing.dart';
import '../constants/app_typography.dart';
import 'custom_card.dart';

class RoomCard extends StatelessWidget {
  final RoomModel room;
  final VoidCallback onTap;

  const RoomCard({
    super.key,
    required this.room,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final availableCount = room.availableBedsCount;
    final totalCount = room.totalBedsCount;
    final bookedCount = totalCount - availableCount;

    return CustomCard(
      onTap: onTap,
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image Header with Responsive Visual Badges
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(AppSpacing.radiusCard)),
                child: Image.network(
                  room.images.first,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => _buildFallbackHeader(),
                ),
              ),
              
              // Responsive Top Badges Row (No Overlap on Mobile)
              Positioned(
                top: 10,
                left: 10,
                right: 10,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Left Badges: Floor & AC/Non-AC Badge
                    Flexible(
                      child: Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: [
                          // Floor Badge
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.95),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'Floor ${room.floor}',
                              style: AppTypography.badge.copyWith(color: Colors.white, fontSize: 10),
                            ),
                          ),
                          // AC / Non-AC Icon Badge
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: room.isAc
                                  ? const Color(0xFF0284C7).withValues(alpha: 0.95)
                                  : AppColors.textSecondary.withValues(alpha: 0.95),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  room.isAc ? Icons.ac_unit_rounded : Icons.air_rounded,
                                  size: 12,
                                  color: Colors.white,
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  room.isAc ? 'AC' : 'NON-AC',
                                  style: AppTypography.badge.copyWith(color: Colors.white, fontSize: 10),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Right Sharing Type Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.secondary.withValues(alpha: 0.95),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            _getSharingIcon(room.sharingType),
                            size: 12,
                            color: Colors.white,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            room.sharingType.toUpperCase(),
                            style: AppTypography.badge.copyWith(color: Colors.white, fontSize: 10),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          // Room Details Body
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Room Title & Monthly Rent
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Room ${room.roomNumber}',
                      style: AppTypography.titleLarge,
                    ),
                    Text(
                      '₹${room.monthlyRent.toInt()}/mo',
                      style: AppTypography.titleLarge.copyWith(color: AppColors.secondary),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.xs),
                Text(
                  'Deposit ₹${room.securityDeposit.toInt()} • Size: ${room.roomSize}',
                  style: AppTypography.bodyMedium,
                ),
                const SizedBox(height: AppSpacing.md),

                // BOOKMYSHOW CINEMA SEAT STYLE VISUAL BED STATUS BAR
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.divider),
                  ),
                  child: Row(
                    children: [
                      // Mini Bed Icons Row
                      Row(
                        children: room.beds.map((bed) {
                          return Padding(
                            padding: const EdgeInsets.only(right: 6),
                            child: _buildMiniBedIcon(bed.status),
                          );
                        }).toList(),
                      ),
                      const Spacer(),
                      // Status Text Denotion (e.g. 2 Booked • 1 Left)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: availableCount > 0
                              ? AppColors.success.withValues(alpha: 0.12)
                              : AppColors.error.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: availableCount > 0
                                ? AppColors.success.withValues(alpha: 0.3)
                                : AppColors.error.withValues(alpha: 0.3),
                          ),
                        ),
                        child: Text(
                          bookedCount > 0
                              ? '$bookedCount Booked • $availableCount Left'
                              : '$availableCount Left Available',
                          style: AppTypography.badge.copyWith(
                            color: availableCount > 0 ? AppColors.success : AppColors.error,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.md),

                // Furniture Tags
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: room.furniture
                      .take(3)
                      .map(
                        (f) => Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.divider),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.check_circle_outline, size: 12, color: AppColors.accent),
                              const SizedBox(width: 4),
                              Text(f, style: AppTypography.caption),
                            ],
                          ),
                        ),
                      )
                      .toList(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFallbackHeader() {
    return Container(
      height: 180,
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          Icon(
            Icons.bed_rounded,
            size: 64,
            color: Colors.white.withValues(alpha: 0.15),
          ),
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const SizedBox(height: 30),
              const Icon(Icons.king_bed_rounded, size: 36, color: Colors.white),
              const SizedBox(height: 6),
              Text(
                'Rudra PG Room ${room.roomNumber}',
                style: AppTypography.caption.copyWith(color: Colors.white70, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
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

  Widget _buildMiniBedIcon(BedStatus status) {
    Color bg;
    IconData icon;

    switch (status) {
      case BedStatus.available:
        bg = AppColors.bedAvailable;
        icon = Icons.king_bed_rounded;
        break;
      case BedStatus.occupied:
        bg = AppColors.bedOccupied;
        icon = Icons.person_rounded;
        break;
      case BedStatus.reserved:
        bg = AppColors.bedReserved;
        icon = Icons.lock_clock_rounded;
        break;
      case BedStatus.selected:
        bg = AppColors.bedSelected;
        icon = Icons.check_rounded;
        break;
    }

    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Icon(icon, size: 14, color: Colors.white),
    );
  }
}
