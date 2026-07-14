import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/models.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import 'payment_screen.dart';

class OrderDetailScreen extends StatefulWidget {
  final Order order;
  final List<MenuItem> menuItems;
  final VoidCallback onUpdate;

  const OrderDetailScreen({
    super.key,
    required this.order,
    required this.menuItems,
    required this.onUpdate,
  });

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  final _api = ApiService();
  late Order _order;
  bool _isUpdating = false;
  bool _isSendingBill = false;

  @override
  void initState() {
    super.initState();
    _order = widget.order;
  }

  bool get _showPaymentActions =>
      _order.status == 'served' &&
      (!_order.isWhatsAppOrder || _order.billAlreadySent);

  List<String> get _nextStatuses {
    // Canonical: received → accepted → preparing → ready → served → completed
    // Legacy aliases still accepted from older API payloads.
    switch (_order.status) {
      case 'pending':
      case 'received':
        return ['accepted'];
      case 'confirmed':
      case 'accepted':
        return ['preparing'];
      case 'preparing':
        return ['ready'];
      case 'ready':
        return ['served'];
      case 'served':
        if (_order.isWhatsAppOrder && !_order.billAlreadySent) {
          return [];
        }
        return ['completed'];
      default:
        return [];
    }
  }

  Future<void> _updateStatus(String newStatus) async {
    setState(() => _isUpdating = true);
    HapticFeedback.lightImpact();
    try {
      final updated = await _api.updateOrderStatus(_order.id, newStatus);
      setState(() {
        _order = updated;
        _isUpdating = false;
      });
      widget.onUpdate();
      _showSnack('Status imebadilishwa → $newStatus ✓', AppTheme.success);
    } catch (e) {
      setState(() => _isUpdating = false);
      _showSnack(e.toString(), AppTheme.error);
    }
  }

  Future<void> _sendWhatsAppBill({required bool force}) async {
    final confirmMessage = force && _order.billAlreadySent
        ? 'Tuma tena picha ya bili kwa WhatsApp ya mteja?'
        : 'Thibitisha order na kutuma picha ya bili kwa WhatsApp ya mteja?';

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: AppTheme.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          force && _order.billAlreadySent ? 'Tuma tena Bili' : 'Thibitisha Order',
          style: GoogleFonts.poppins(
            color: AppTheme.textPrimary,
            fontWeight: FontWeight.w600,
          ),
        ),
        content: Text(
          confirmMessage,
          style: GoogleFonts.poppins(color: AppTheme.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Hapana'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(force && _order.billAlreadySent ? 'Tuma tena' : 'Tuma Bili'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isSendingBill = true);
    HapticFeedback.mediumImpact();
    try {
      final updated = await _api.sendWhatsAppBill(_order.id, force: force);
      setState(() {
        _order = updated;
        _isSendingBill = false;
      });
      widget.onUpdate();
      _showSnack(
        force && updated.billAlreadySent
            ? 'Bili imetumwa WhatsApp ✓'
            : 'Order imethibitishwa, bili imetumwa ✓',
        AppTheme.success,
      );
    } catch (e) {
      setState(() => _isSendingBill = false);
      _showSnack(e.toString(), AppTheme.error);
    }
  }

  Future<void> _delete() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: AppTheme.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Futa Order #${_order.id}?',
            style: GoogleFonts.poppins(
                color: AppTheme.textPrimary, fontWeight: FontWeight.w600)),
        content: Text('Hatua hii haiwezi kubatilishwa.',
            style: GoogleFonts.poppins(color: AppTheme.textSecondary)),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Hapana')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.error),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Futa'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      try {
        await _api.deleteOrder(_order.id);
        widget.onUpdate();
        if (mounted) Navigator.pop(context);
      } catch (e) {
        _showSnack(e.toString(), AppTheme.error);
      }
    }
  }

  void _showSnack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color.withOpacity(0.9),
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  void _openPaymentScreen() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => PaymentScreen(
          order: _order,
          onPaid: () {
            widget.onUpdate();
            Navigator.of(context).popUntil(
                (route) => route.isFirst || route.settings.name == '/orders');
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isTablet = size.width > 600;
    final currency = NumberFormat('#,##0', 'en_US');
    final dateFormat = DateFormat('dd MMM yyyy, HH:mm');
    final statusColor = AppTheme.getStatusColor(_order.status);
    final statusIcon = AppTheme.getStatusIcon(_order.status);

    return Scaffold(
      backgroundColor: AppTheme.bg,
      body: CustomScrollView(
        slivers: [
          // Hero AppBar
          SliverAppBar(
            expandedHeight: 200,
            pinned: true,
            backgroundColor: AppTheme.surface,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded,
                  color: AppTheme.textPrimary),
              onPressed: () => Navigator.pop(context),
            ),
            actions: [
              IconButton(
                onPressed: _delete,
                icon: const Icon(Icons.delete_outline_rounded,
                    color: AppTheme.error),
                tooltip: 'Futa Order',
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      statusColor.withOpacity(0.2),
                      AppTheme.surface,
                    ],
                  ),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 56, 20, 16),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        // Status icon
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          width: isTablet ? 80 : 64,
                          height: isTablet ? 80 : 64,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: statusColor.withOpacity(0.15),
                            border: Border.all(color: statusColor, width: 2),
                          ),
                          child: Icon(statusIcon,
                              color: statusColor, size: isTablet ? 38 : 30),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                'Order #${_order.id}',
                                style: GoogleFonts.poppins(
                                  fontSize: isTablet ? 28 : 22,
                                  fontWeight: FontWeight.w800,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 10, vertical: 3),
                                decoration: BoxDecoration(
                                  color: statusColor.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                      color: statusColor.withOpacity(0.4)),
                                ),
                                child: Text(
                                  _order.status.toUpperCase(),
                                  style: GoogleFonts.poppins(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: statusColor,
                                    letterSpacing: 1,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                dateFormat.format(_order.createdAt.toLocal()),
                                style: GoogleFonts.poppins(
                                  fontSize: 12,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

          SliverPadding(
            padding: EdgeInsets.all(isTablet ? 24 : 16),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                // Info cards
                _buildInfoRow(isTablet),
                const SizedBox(height: 16),

                // Items
                _buildItemsList(currency).animate().fadeIn(delay: 200.ms),
                const SizedBox(height: 16),

                // Total
                _buildTotalCard(currency).animate().fadeIn(delay: 300.ms),
                const SizedBox(height: 16),

                if (_order.status == 'served' && _order.isWhatsAppOrder)
                  _buildBillStatusCard(dateFormat)
                      .animate()
                      .fadeIn(delay: 350.ms),
                if (_order.status == 'served' && _order.isWhatsAppOrder)
                  const SizedBox(height: 12),

                // Status actions
                if (_isUpdating || _isSendingBill)
                  const Center(
                      child: CircularProgressIndicator(color: AppTheme.primary))
                else ...[
                  if (_order.canSendWhatsAppBill && !_order.billAlreadySent)
                    _buildWhatsAppBillButton(
                      label: 'Thibitisha Order',
                      icon: Icons.chat_rounded,
                      color: AppTheme.statusServed,
                      force: true,
                    ).animate().fadeIn(delay: 380.ms),
                  if (_order.canResendWhatsAppBill)
                    _buildWhatsAppBillButton(
                      label: 'Tuma tena Bili',
                      icon: Icons.refresh_rounded,
                      color: Colors.amber,
                      force: true,
                    ).animate().fadeIn(delay: 390.ms),
                  if (_nextStatuses.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    _buildStatusActions().animate().fadeIn(delay: 400.ms),
                  ],
                  const SizedBox(height: 12),
                  if (_showPaymentActions)
                    _buildPaymentButton().animate().fadeIn(delay: 450.ms),
                ],

                const SizedBox(height: 80),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(bool isTablet) {
    final infos = [
      (Icons.table_restaurant_rounded, 'Meza', _order.tableNumber),
      if (_order.customerName != null && _order.customerName!.isNotEmpty)
        (Icons.person_outline_rounded, 'Mteja', _order.customerName!),
      if (_order.customerPhone != null && _order.customerPhone!.isNotEmpty)
        (Icons.phone_outlined, 'Simu', _order.customerPhone!),
      if (_order.isWhatsAppOrder)
        (
          Icons.chat_rounded,
          'WhatsApp',
          _order.billAlreadySent ? 'Bili imetumwa' : 'Bili inasubiri kutumwa'
        ),
    ];

    return Wrap(
      spacing: 10,
      runSpacing: 10,
      children: infos
          .map((info) => Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: AppTheme.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppTheme.border),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(info.$1, color: AppTheme.primary, size: 16),
                    const SizedBox(width: 8),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(info.$2,
                            style: GoogleFonts.poppins(
                                fontSize: 10, color: AppTheme.textMuted)),
                        Text(info.$3,
                            style: GoogleFonts.poppins(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textPrimary)),
                      ],
                    ),
                  ],
                ),
              ))
          .toList(),
    ).animate().fadeIn(delay: 100.ms).slideY(begin: 0.1);
  }

  Widget _buildItemsList(NumberFormat currency) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text(
              'Items (${_order.items.length})',
              style: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimary,
              ),
            ),
          ),
          const Divider(height: 1, color: AppTheme.border),
          ...List.generate(_order.items.length, (i) {
            final item = _order.items[i];
            return Column(
              children: [
                Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Center(
                          child: Text(
                            '${item.quantity}',
                            style: GoogleFonts.poppins(
                              fontWeight: FontWeight.w700,
                              color: AppTheme.primary,
                              fontSize: 14,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(item.name,
                                style: GoogleFonts.poppins(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: AppTheme.textPrimary,
                                )),
                            Text(
                              'TZS ${currency.format(item.price)} × ${item.quantity}',
                              style: GoogleFonts.poppins(
                                fontSize: 11,
                                color: AppTheme.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        'TZS ${currency.format(item.total)}',
                        style: GoogleFonts.poppins(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                    ],
                  ),
                ),
                if (i < _order.items.length - 1)
                  const Divider(
                      height: 1,
                      color: AppTheme.border,
                      indent: 16,
                      endIndent: 16),
              ],
            );
          }),
        ],
      ),
    );
  }

  Widget _buildTotalCard(NumberFormat currency) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.success.withOpacity(0.1),
            AppTheme.success.withOpacity(0.05),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.success.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              const Icon(Icons.receipt_long_rounded,
                  color: AppTheme.success, size: 22),
              const SizedBox(width: 10),
              Text('Jumla ya Bili',
                  style: GoogleFonts.poppins(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimary,
                  )),
            ],
          ),
          Text(
            'TZS ${currency.format(_order.totalAmount)}',
            style: GoogleFonts.poppins(
              fontSize: 20,
              fontWeight: FontWeight.w800,
              color: AppTheme.success,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBillStatusCard(DateFormat dateFormat) {
    final sent = _order.billAlreadySent;
    final color = sent ? AppTheme.success : Colors.amber;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withOpacity(0.25)),
      ),
      child: Row(
        children: [
          Icon(
            sent ? Icons.check_circle_rounded : Icons.schedule_send_rounded,
            color: color,
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  sent ? 'Bili imetumwa WhatsApp' : 'Bili haijatumwa bado',
                  style: GoogleFonts.poppins(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  sent
                      ? 'Imetumwa ${_order.billImagePushedAt != null ? dateFormat.format(_order.billImagePushedAt!.toLocal()) : 'hivi karibuni'}'
                      : 'Bonyeza Thibitisha Order kutuma picha ya bili kwa mteja.',
                  style: GoogleFonts.poppins(
                    fontSize: 11,
                    color: AppTheme.textSecondary,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWhatsAppBillButton({
    required String label,
    required IconData icon,
    required Color color,
    required bool force,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: SizedBox(
        width: double.infinity,
        height: 52,
        child: ElevatedButton.icon(
          style: ElevatedButton.styleFrom(
            backgroundColor: color.withOpacity(0.15),
            foregroundColor: color,
            side: BorderSide(color: color.withOpacity(0.35)),
            elevation: 0,
          ),
          onPressed: () => _sendWhatsAppBill(force: force),
          icon: Icon(icon, size: 20),
          label: Text(
            label,
            style: GoogleFonts.poppins(fontWeight: FontWeight.w700, fontSize: 15),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusActions() {
    final statusLabels = {
      'preparing': (
        'Anza Kupika',
        AppTheme.statusPreparing,
        Icons.restaurant_rounded
      ),
      'served': (
        'Mhudumie Mteja',
        AppTheme.statusServed,
        Icons.room_service_rounded
      ),
      'paid': (
        'Thibitisha Malipo (Cash/WhatsApp)',
        AppTheme.statusPaid,
        Icons.payments_rounded
      ),
    };

    return Column(
      children: _nextStatuses.map((status) {
        final config = statusLabels[status]!;
        return Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: config.$2.withOpacity(0.15),
                foregroundColor: config.$2,
                side: BorderSide(color: config.$2.withOpacity(0.4)),
                elevation: 0,
              ),
              onPressed: () => _updateStatus(status),
              icon: Icon(config.$3, size: 18),
              label: Text(config.$1,
                  style: GoogleFonts.poppins(fontWeight: FontWeight.w600)),
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPaymentButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton.icon(
        onPressed: _openPaymentScreen,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primary,
          foregroundColor: Colors.white,
        ),
        icon: const Icon(Icons.phone_in_talk_rounded, size: 20),
        label: Text(
          'Lipa kwa Selcom USSD',
          style: GoogleFonts.poppins(fontSize: 15, fontWeight: FontWeight.w700),
        ),
      ),
    );
  }
}
