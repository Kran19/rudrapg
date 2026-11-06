import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../home/home_dashboard_screen.dart';
import '../payments/payments_screen.dart';
import '../notifications/notifications_screen.dart';
import '../profile/profile_screen.dart';
import '../home/data/student_repository.dart';

class MainLayoutScreen extends ConsumerStatefulWidget {
  final int initialIndex;
  const MainLayoutScreen({super.key, this.initialIndex = 0});

  @override
  ConsumerState<MainLayoutScreen> createState() => _MainLayoutScreenState();
}

class _MainLayoutScreenState extends ConsumerState<MainLayoutScreen>
    with WidgetsBindingObserver {
  late int _currentIndex;
  Timer? _realtimeSyncTimer;

  final List<Widget> _screens = const [
    HomeDashboardScreen(),
    PaymentsScreen(),
    NotificationsScreen(),
    ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    WidgetsBinding.instance.addObserver(this);

    // Initial background sync
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _syncRealtimeData();
    });

    // Real-time background sync every 12 seconds without user having to refresh or re-login
    _realtimeSyncTimer = Timer.periodic(const Duration(seconds: 12), (_) {
      _syncRealtimeData();
    });
  }

  void _syncRealtimeData() {
    if (!mounted) return;
    ref.invalidate(studentProfileProvider);
    ref.invalidate(paymentHistoryProvider);
    ref.invalidate(complaintHistoryProvider);
    ref.invalidate(electricityHistoryProvider);
    ref.invalidate(noticeHistoryProvider);
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _syncRealtimeData();
    }
  }

  @override
  void dispose() {
    _realtimeSyncTimer?.cancel();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: AppColors.divider, width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
            _syncRealtimeData();
          },
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home_rounded),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.account_balance_wallet_outlined),
              activeIcon: Icon(Icons.account_balance_wallet_rounded),
              label: 'Payments',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.notifications_outlined),
              activeIcon: Icon(Icons.notifications_rounded),
              label: 'Alerts',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline_rounded),
              activeIcon: Icon(Icons.person_rounded),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }
}
