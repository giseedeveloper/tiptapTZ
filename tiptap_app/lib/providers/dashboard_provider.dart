import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../core/config.dart';
import '../models/dashboard_model.dart';
import '../services/api_service.dart';

class DashboardProvider with ChangeNotifier {
  Timer? _statsTimer;
  int _pollCount = 0;

  DashboardData? _data;
  DashboardStats? _stats;
  bool _isLoading = false;
  bool _isTogglingStatus = false;
  bool _isDismissingRoster = false;
  String? _error;
  int _lastRosterNotificationCount = 0;

  DashboardData? get data => _data;
  DashboardStats? get stats => _stats;
  bool get isLoading => _isLoading;
  bool get isTogglingStatus => _isTogglingStatus;
  bool get isDismissingRoster => _isDismissingRoster;
  bool get isOnline => _data?.isOnline ?? true;
  bool get isLinked => _data?.isLinked ?? true;
  String? get error => _error;

  Future<void> loadFull(ApiService api) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final dashboardData = await api.getDashboard();
      await _applyDashboard(dashboardData);
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      _error = 'Failed to load dashboard';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _applyDashboard(DashboardData dashboardData) async {
    final newCount = dashboardData.rosterNotifications.length;
    if (_lastRosterNotificationCount > 0 && newCount > _lastRosterNotificationCount) {
      await _showRosterLocalNotification(dashboardData.rosterNotifications.first.message);
    }
    _lastRosterNotificationCount = newCount;

    _data = dashboardData;
    _stats = dashboardData.stats;
  }

  Future<void> _showRosterLocalNotification(String body) async {
    const android = AndroidNotificationDetails(
      'roster_updates',
      'Roster & Tables',
      channelDescription: 'Manager table and shift updates',
      importance: Importance.high,
      priority: Priority.high,
    );
    const details = NotificationDetails(android: android);
    final plugin = FlutterLocalNotificationsPlugin();
    await plugin.show(
      DateTime.now().millisecondsSinceEpoch.remainder(100000),
      'TIPTAP · Roster Update',
      body,
      details,
    );
  }

  Future<void> refreshStats(ApiService api) async {
    _pollCount++;
    if (_pollCount % 3 == 0) {
      try {
        final dashboardData = await api.getDashboard();
        await _applyDashboard(dashboardData);
        notifyListeners();
      } catch (_) {}
      return;
    }

    try {
      final newStats = await api.getDashboardStats();
      if (_stats != newStats) {
        _stats = newStats;
        if (_data != null) {
          _data = _data!.copyWith(stats: newStats);
        }
        notifyListeners();
      }
    } catch (_) {}
  }

  void startStatsPolling(ApiService api) {
    _statsTimer?.cancel();
    refreshStats(api);
    _statsTimer = Timer.periodic(
      const Duration(seconds: AppConfig.statsPollIntervalSeconds),
      (_) => refreshStats(api),
    );
  }

  void stopStatsPolling() {
    _statsTimer?.cancel();
    _statsTimer = null;
    _pollCount = 0;
  }

  Future<bool> claimOrder(ApiService api, int orderId) async {
    try {
      await api.claimOrder(orderId);
      await loadFull(api);
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  Future<bool> completeRequest(ApiService api, int requestId) async {
    try {
      await api.completeRequest(requestId);
      await loadFull(api);
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  Future<bool> dismissRosterNotifications(ApiService api) async {
    _isDismissingRoster = true;
    notifyListeners();
    try {
      await api.dismissRosterNotifications();
      if (_data != null) {
        _data = _data!.copyWith(rosterNotifications: []);
        _lastRosterNotificationCount = 0;
      }
      _isDismissingRoster = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isDismissingRoster = false;
      notifyListeners();
      return false;
    } catch (_) {
      _isDismissingRoster = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> toggleOnlineStatus(ApiService api, bool goOnline) async {
    _isTogglingStatus = true;
    notifyListeners();
    try {
      await api.updateWaiterStatus(goOnline);
      if (_data != null) {
        _data = _data!.copyWith(isOnline: goOnline);
      }
      _isTogglingStatus = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isTogglingStatus = false;
      notifyListeners();
      return false;
    } catch (_) {
      _isTogglingStatus = false;
      notifyListeners();
      return false;
    }
  }
}
