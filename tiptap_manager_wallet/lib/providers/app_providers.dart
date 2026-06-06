import 'package:flutter/foundation.dart';

import '../models/wallet_models.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._api, this._storage);

  final ApiService _api;
  final StorageService _storage;

  AuthSession? _session;
  bool _bootstrapping = true;
  String? _error;

  AuthSession? get session => _session;
  bool get isAuthenticated => _session != null;
  bool get bootstrapping => _bootstrapping;
  String? get error => _error;

  Future<void> bootstrap() async {
    _session = await _storage.loadSession();
    _bootstrapping = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _error = null;
    notifyListeners();

    try {
      _session = await _api.login(email: email, password: password);
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (_) {
      _error = 'Unable to connect. Check your network.';
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _api.logout();
    _session = null;
    notifyListeners();
  }
}

class WalletProvider extends ChangeNotifier {
  WalletProvider(this._api);

  final ApiService _api;

  WalletSnapshot? _snapshot;
  bool _loadingSummary = false;
  String? _summaryError;

  List<WalletPayment> _payments = [];
  List<WalletWithdrawal> _withdrawals = [];
  bool _loadingPayments = false;
  bool _loadingWithdrawals = false;
  int _paymentsPage = 1;
  int _withdrawalsPage = 1;
  bool _paymentsHasMore = false;
  bool _withdrawalsHasMore = false;

  WalletSnapshot? get snapshot => _snapshot;
  bool get loadingSummary => _loadingSummary;
  String? get summaryError => _summaryError;
  List<WalletPayment> get payments => _payments;
  List<WalletWithdrawal> get withdrawals => _withdrawals;
  bool get loadingPayments => _loadingPayments;
  bool get loadingWithdrawals => _loadingWithdrawals;

  Future<void> loadSummary({bool force = false}) async {
    if (_loadingSummary && !force) return;

    _loadingSummary = true;
    _summaryError = null;
    notifyListeners();

    try {
      _snapshot = await _api.fetchWalletSummary();
    } on ApiException catch (e) {
      _summaryError = e.message;
    } catch (_) {
      _summaryError = 'Failed to load wallet.';
    } finally {
      _loadingSummary = false;
      notifyListeners();
    }
  }

  Future<void> refreshAll() async {
    await loadSummary(force: true);
    await loadPayments(refresh: true);
    await loadWithdrawals(refresh: true);
  }

  Future<void> loadPayments({bool refresh = false}) async {
    if (_loadingPayments) return;

    if (refresh) {
      _paymentsPage = 1;
      _payments = [];
    }

    _loadingPayments = true;
    notifyListeners();

    try {
      final page = await _api.fetchPayments(page: _paymentsPage);
      _payments = refresh ? page.items : [..._payments, ...page.items];
      _paymentsHasMore = page.hasMore;
      if (_paymentsHasMore) _paymentsPage++;
    } catch (_) {
      // keep silent on pagination errors
    } finally {
      _loadingPayments = false;
      notifyListeners();
    }
  }

  Future<void> loadWithdrawals({bool refresh = false}) async {
    if (_loadingWithdrawals) return;

    if (refresh) {
      _withdrawalsPage = 1;
      _withdrawals = [];
    }

    _loadingWithdrawals = true;
    notifyListeners();

    try {
      final page = await _api.fetchWithdrawals(page: _withdrawalsPage);
      _withdrawals = refresh ? page.items : [..._withdrawals, ...page.items];
      _withdrawalsHasMore = page.hasMore;
      if (_withdrawalsHasMore) _withdrawalsPage++;
    } catch (_) {
      // keep silent
    } finally {
      _loadingWithdrawals = false;
      notifyListeners();
    }
  }

  Future<PayoutProfile> savePayoutProfile({
    required String method,
    required String details,
  }) async {
    final profile = await _api.updatePayoutProfile(
      payoutMethod: method,
      payoutDetails: details,
    );
    await loadSummary(force: true);
    return profile;
  }

  Future<WalletWithdrawal> submitWithdrawal({
    required double amount,
    required bool useSavedPayout,
    String? paymentMethod,
    String? paymentDetails,
  }) async {
    final withdrawal = await _api.submitWithdrawal(
      amount: amount,
      useSavedPayout: useSavedPayout,
      paymentMethod: paymentMethod,
      paymentDetails: paymentDetails,
    );
    await refreshAll();
    return withdrawal;
  }
}
