class AuthUser {
  AuthUser({
    required this.id,
    required this.name,
    required this.email,
    required this.restaurantId,
    required this.restaurantName,
    required this.roles,
  });

  final int id;
  final String name;
  final String email;
  final int? restaurantId;
  final String? restaurantName;
  final List<String> roles;

  bool get isManager => roles.contains('manager');

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      restaurantId: json['restaurant_id'] as int?,
      restaurantName: json['restaurant_name'] as String?,
      roles: (json['roles'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
    );
  }
}

class AuthSession {
  AuthSession({required this.token, required this.user});

  final String token;
  final AuthUser user;

  Map<String, dynamic> toJson() => {
        'token': token,
        'user': {
          'id': user.id,
          'name': user.name,
          'email': user.email,
          'restaurant_id': user.restaurantId,
          'restaurant_name': user.restaurantName,
          'roles': user.roles,
        },
      };

  factory AuthSession.fromJson(Map<String, dynamic> json) {
    return AuthSession(
      token: json['token'] as String,
      user: AuthUser.fromJson(json['user'] as Map<String, dynamic>),
    );
  }
}

class WalletSummary {
  WalletSummary({
    required this.totalEarned,
    required this.commissionRate,
    required this.platformCommission,
    required this.netEarned,
    required this.totalWithdrawn,
    required this.pendingWithdrawals,
    required this.availableBalance,
  });

  final double totalEarned;
  final double commissionRate;
  final double platformCommission;
  final double netEarned;
  final double totalWithdrawn;
  final double pendingWithdrawals;
  final double availableBalance;

  factory WalletSummary.fromJson(Map<String, dynamic> json) {
    double n(dynamic v) => (v as num?)?.toDouble() ?? 0;

    return WalletSummary(
      totalEarned: n(json['total_earned']),
      commissionRate: n(json['commission_rate']),
      platformCommission: n(json['platform_commission']),
      netEarned: n(json['net_earned']),
      totalWithdrawn: n(json['total_withdrawn']),
      pendingWithdrawals: n(json['pending_withdrawals']),
      availableBalance: n(json['available_balance']),
    );
  }
}

class BreakdownRow {
  BreakdownRow({
    required this.label,
    required this.total,
    required this.count,
  });

  final String label;
  final double total;
  final int count;

  factory BreakdownRow.fromTypeJson(Map<String, dynamic> json) {
    return BreakdownRow(
      label: json['type'] as String? ?? 'order',
      total: (json['total'] as num?)?.toDouble() ?? 0,
      count: json['count'] as int? ?? 0,
    );
  }

  factory BreakdownRow.fromMethodJson(Map<String, dynamic> json) {
    return BreakdownRow(
      label: json['method'] as String? ?? 'unknown',
      total: (json['total'] as num?)?.toDouble() ?? 0,
      count: json['count'] as int? ?? 0,
    );
  }
}

class PaymentBreakdown {
  PaymentBreakdown({required this.byType, required this.byMethod});

  final List<BreakdownRow> byType;
  final List<BreakdownRow> byMethod;

  factory PaymentBreakdown.fromJson(Map<String, dynamic> json) {
    return PaymentBreakdown(
      byType: (json['by_type'] as List<dynamic>? ?? [])
          .map((e) => BreakdownRow.fromTypeJson(e as Map<String, dynamic>))
          .toList(),
      byMethod: (json['by_method'] as List<dynamic>? ?? [])
          .map((e) => BreakdownRow.fromMethodJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class PayoutProfile {
  PayoutProfile({
    required this.payoutMethod,
    required this.payoutDetails,
    required this.isComplete,
  });

  final String? payoutMethod;
  final String? payoutDetails;
  final bool isComplete;

  factory PayoutProfile.fromJson(Map<String, dynamic> json) {
    return PayoutProfile(
      payoutMethod: json['payout_method'] as String?,
      payoutDetails: json['payout_details'] as String?,
      isComplete: json['is_complete'] == true,
    );
  }
}

class WalletSnapshot {
  WalletSnapshot({
    required this.restaurantName,
    required this.summary,
    required this.breakdown,
    required this.payoutProfile,
    required this.minWithdrawal,
    required this.currencySymbol,
  });

  final String restaurantName;
  final WalletSummary summary;
  final PaymentBreakdown breakdown;
  final PayoutProfile payoutProfile;
  final double minWithdrawal;
  final String currencySymbol;

  factory WalletSnapshot.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>;
    final restaurant = data['restaurant'] as Map<String, dynamic>? ?? {};

    return WalletSnapshot(
      restaurantName: restaurant['name'] as String? ?? 'Restaurant',
      summary: WalletSummary.fromJson(
        data['summary'] as Map<String, dynamic>,
      ),
      breakdown: PaymentBreakdown.fromJson(
        data['breakdown'] as Map<String, dynamic>? ?? {},
      ),
      payoutProfile: PayoutProfile.fromJson(
        data['payout_profile'] as Map<String, dynamic>? ?? {},
      ),
      minWithdrawal: (data['min_withdrawal'] as num?)?.toDouble() ?? 0,
      currencySymbol: data['currency_symbol'] as String? ?? 'Tsh',
    );
  }
}

class WalletPayment {
  WalletPayment({
    required this.id,
    required this.amount,
    required this.method,
    required this.paymentType,
    required this.status,
    required this.reference,
    required this.orderId,
    required this.tableNumber,
    required this.createdAt,
  });

  final int id;
  final double amount;
  final String? method;
  final String paymentType;
  final String status;
  final String? reference;
  final int? orderId;
  final String? tableNumber;
  final DateTime? createdAt;

  factory WalletPayment.fromJson(Map<String, dynamic> json) {
    return WalletPayment(
      id: json['id'] as int,
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
      method: json['method'] as String?,
      paymentType: json['payment_type'] as String? ?? 'order',
      status: json['status'] as String? ?? '',
      reference: json['transaction_reference'] as String?,
      orderId: json['order_id'] as int?,
      tableNumber: json['table_number'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }
}

class WalletWithdrawal {
  WalletWithdrawal({
    required this.id,
    required this.amount,
    required this.status,
    required this.paymentMethod,
    required this.paymentDetails,
    required this.adminNote,
    required this.createdAt,
  });

  final int id;
  final double amount;
  final String status;
  final String? paymentMethod;
  final String? paymentDetails;
  final String? adminNote;
  final DateTime? createdAt;

  factory WalletWithdrawal.fromJson(Map<String, dynamic> json) {
    return WalletWithdrawal(
      id: json['id'] as int,
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String? ?? '',
      paymentMethod: json['payment_method'] as String?,
      paymentDetails: json['payment_details'] as String?,
      adminNote: json['admin_note'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }
}

class Paginated<T> {
  Paginated({required this.items, required this.currentPage, required this.lastPage});

  final List<T> items;
  final int currentPage;
  final int lastPage;

  bool get hasMore => currentPage < lastPage;
}
