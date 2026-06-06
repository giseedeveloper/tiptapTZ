import 'dart:convert';

import 'package:http/http.dart' as http;

import '../core/config.dart';
import '../models/wallet_models.dart';
import 'storage_service.dart';

class ApiException implements Exception {
  ApiException(this.message, {this.errors});

  final String message;
  final Map<String, dynamic>? errors;

  @override
  String toString() => message;
}

class UnauthorizedException implements Exception {
  @override
  String toString() => 'Session expired. Please sign in again.';
}

class ApiService {
  ApiService(this._storage);

  final StorageService _storage;

  String get _base => AppConfig.apiBaseUrl;

  Future<Map<String, String>> _headers({bool auth = false}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (auth) {
      final session = await _storage.loadSession();
      if (session != null) {
        headers['Authorization'] = 'Bearer ${session.token}';
      }
    }

    return headers;
  }

  Future<http.Response> _send(Future<http.Response> Function() call) async {
    final response = await call();
    if (response.statusCode == 401) {
      await _storage.clearSession();
      throw UnauthorizedException();
    }
    return response;
  }

  Future<AuthSession> login({
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$_base/auth/login'),
      headers: await _headers(),
      body: jsonEncode({'email': email, 'password': password}),
    );

    final body = jsonDecode(response.body) as Map<String, dynamic>;

    if (response.statusCode != 200 || body['success'] != true) {
      throw ApiException(body['message'] as String? ?? 'Login failed.');
    }

    final user = AuthUser.fromJson(body['user'] as Map<String, dynamic>);
    if (!user.isManager) {
      throw ApiException('This app is for restaurant managers only.');
    }

    final session = AuthSession(
      token: body['token'] as String,
      user: user,
    );

    await _storage.saveSession(session);
    return session;
  }

  Future<void> logout() async {
    try {
      await _send(() async => http.post(
            Uri.parse('$_base/auth/logout'),
            headers: await _headers(auth: true),
          ));
    } catch (_) {}
    await _storage.clearSession();
  }

  Future<WalletSnapshot> fetchWalletSummary() async {
    final response = await _send(() async => http.get(
          Uri.parse('$_base/v1/manager/wallet'),
          headers: await _headers(auth: true),
        ));

    final body = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode != 200 || body['success'] != true) {
      throw ApiException(body['message'] as String? ?? 'Failed to load wallet.');
    }

    return WalletSnapshot.fromJson(body);
  }

  Future<Paginated<WalletPayment>> fetchPayments({int page = 1}) async {
    final response = await _send(() async => http.get(
          Uri.parse('$_base/v1/manager/wallet/payments?page=$page'),
          headers: await _headers(auth: true),
        ));

    return _parsePaginated(response, WalletPayment.fromJson);
  }

  Future<Paginated<WalletWithdrawal>> fetchWithdrawals({int page = 1}) async {
    final response = await _send(() async => http.get(
          Uri.parse('$_base/v1/manager/wallet/withdrawals?page=$page'),
          headers: await _headers(auth: true),
        ));

    return _parsePaginated(response, WalletWithdrawal.fromJson);
  }

  Future<PayoutProfile> updatePayoutProfile({
    required String payoutMethod,
    required String payoutDetails,
  }) async {
    final response = await _send(() async => http.put(
          Uri.parse('$_base/v1/manager/wallet/payout-profile'),
          headers: await _headers(auth: true),
          body: jsonEncode({
            'payout_method': payoutMethod,
            'payout_details': payoutDetails,
          }),
        ));

    final body = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode != 200 || body['success'] != true) {
      throw ApiException(body['message'] as String? ?? 'Failed to save profile.');
    }

    return PayoutProfile.fromJson(body['data'] as Map<String, dynamic>);
  }

  Future<WalletWithdrawal> submitWithdrawal({
    required double amount,
    required bool useSavedPayout,
    String? paymentMethod,
    String? paymentDetails,
  }) async {
    final payload = <String, dynamic>{'amount': amount};
    if (useSavedPayout) {
      payload['use_saved_payout'] = true;
    } else {
      payload['payment_method'] = paymentMethod;
      payload['payment_details'] = paymentDetails;
    }

    final response = await _send(() async => http.post(
          Uri.parse('$_base/v1/manager/wallet/withdrawals'),
          headers: await _headers(auth: true),
          body: jsonEncode(payload),
        ));

    final body = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode != 201 || body['success'] != true) {
      throw ApiException(
        body['message'] as String? ?? 'Withdrawal failed.',
        errors: body['errors'] as Map<String, dynamic>?,
      );
    }

    return WalletWithdrawal.fromJson(
      (body['data'] as Map<String, dynamic>)['withdrawal'] as Map<String, dynamic>,
    );
  }

  Paginated<T> _parsePaginated<T>(
    http.Response response,
    T Function(Map<String, dynamic>) fromJson,
  ) {
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode != 200 || body['success'] != true) {
      throw ApiException(body['message'] as String? ?? 'Request failed.');
    }

    final data = body['data'] as Map<String, dynamic>;
    final pagination = data['pagination'] as Map<String, dynamic>? ?? {};
    final items = (data['items'] as List<dynamic>? ?? [])
        .map((e) => fromJson(e as Map<String, dynamic>))
        .toList();

    return Paginated(
      items: items,
      currentPage: pagination['current_page'] as int? ?? 1,
      lastPage: pagination['last_page'] as int? ?? 1,
    );
  }
}
