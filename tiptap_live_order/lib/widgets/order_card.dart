import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/models.dart';
import '../theme/app_theme.dart';

class OrderCard extends StatelessWidget {
  final Order order;
  final VoidCallback onTap;
  final void Function(String) onUpdateStatus;
  final VoidCallback onDelete;
  final Future<void> Function({required bool force})? onSendWhatsAppBill;

  const OrderCard({
    super.key,
    required this.order,
    required this.onTap,
    required this.onUpdateStatus,
    required this.onDelete,
    this.onSendWhatsAppBill,
  });

  @override
  Widget build(BuildContext context) {
    final statusColor = AppTheme.getStatusColor(order.status);
    final statusIcon = AppTheme.getStatusIcon(order.status);
    final currency = NumberFormat('#,##0', 'en_US');
    final timeFormat = DateFormat('HH:mm');
    final isTablet = MediaQuery.of(context).size.width > 600;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header row
            Container(
              padding: const EdgeInsets.fromLTRB(14, 12, 10, 10),
              decoration: BoxDecoration(
                color: statusColor.withOpacity(0.05),
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(15)),
                border: Border(
                    bottom: BorderSide(color: statusColor.withOpacity(0.15))),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(statusIcon, color: statusColor, size: 16),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              'Order #${order.id}',
                              style: GoogleFonts.poppins(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.textPrimary,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: statusColor.withOpacity(0.12),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                order.status.toUpperCase(),
                                style: GoogleFonts.poppins(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w700,
                                  color: statusColor,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            if (order.status == 'served' &&
                                order.isWhatsAppOrder) ...[
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: (order.billAlreadySent
                                          ? AppTheme.success
                                          : Colors.amber)
                                      .withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      order.billAlreadySent
                                          ? Icons.check_rounded
                                          : Icons.chat_rounded,
                                      size: 10,
                                      color: order.billAlreadySent
                                          ? AppTheme.success
                                          : Colors.amber,
                                    ),
                                    const SizedBox(width: 3),
                                    Text(
                                      order.billAlreadySent
                                          ? 'Bili sent'
                                          : 'Bill pending',
                                      style: GoogleFonts.poppins(
                                        fontSize: 8,
                                        fontWeight: FontWeight.w700,
                                        color: order.billAlreadySent
                                            ? AppTheme.success
                                            : Colors.amber,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ],
                        ),
                        Row(
                          children: [
                            const Icon(Icons.table_restaurant_rounded,
                                size: 11, color: AppTheme.textMuted),
                            const SizedBox(width: 3),
                            Text(
                              'Meza ${order.tableNumber}',
                              style: GoogleFonts.poppins(
                                fontSize: 11,
                                color: AppTheme.textSecondary,
                              ),
                            ),
                            if (order.customerName != null &&
                                order.customerName!.isNotEmpty) ...[
                              const Text('  ·  ',
                                  style: TextStyle(color: AppTheme.textMuted)),
                              Flexible(
                                child: Text(
                                  order.customerName!,
                                  style: GoogleFonts.poppins(
                                    fontSize: 11,
                                    color: AppTheme.textSecondary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        timeFormat.format(order.createdAt.toLocal()),
                        style: GoogleFonts.poppins(
                          fontSize: 11,
                          color: AppTheme.textMuted,
                        ),
                      ),
                      const SizedBox(height: 4),
                      PopupMenuButton<String>(
                        padding: EdgeInsets.zero,
                        color: AppTheme.surfaceVariant,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        itemBuilder: (_) => [
                          if (order.status == 'pending')
                            PopupMenuItem(
                              value: 'preparing',
                              child: _menuItem(Icons.restaurant_rounded,
                                  'Anza Kupika', AppTheme.statusPreparing),
                            ),
                          if (order.status == 'preparing')
                            PopupMenuItem(
                              value: 'served',
                              child: _menuItem(Icons.room_service_rounded,
                                  'Mhudumie', AppTheme.statusServed),
                            ),
                          if (order.status == 'served' &&
                              (!order.isWhatsAppOrder || order.billAlreadySent))
                            PopupMenuItem(
                              value: 'paid',
                              child: _menuItem(Icons.payments_rounded,
                                  'Thibitisha Malipo', AppTheme.statusPaid),
                            ),
                          if (order.status == 'served' &&
                              order.isWhatsAppOrder &&
                              !order.billAlreadySent &&
                              onSendWhatsAppBill != null)
                            PopupMenuItem(
                              value: 'whatsapp_bill',
                              child: _menuItem(Icons.chat_rounded,
                                  'Thibitisha Order', AppTheme.statusServed),
                            ),
                          if (order.status == 'served' &&
                              order.canResendWhatsAppBill &&
                              onSendWhatsAppBill != null)
                            PopupMenuItem(
                              value: 'whatsapp_bill_resend',
                              child: _menuItem(Icons.refresh_rounded,
                                  'Tuma tena Bili', Colors.amber),
                            ),
                          const PopupMenuDivider(),
                          PopupMenuItem(
                            value: 'delete',
                            child: _menuItem(Icons.delete_outline_rounded,
                                'Futa Order', AppTheme.error),
                          ),
                        ],
                        onSelected: (v) async {
                          HapticFeedback.selectionClick();
                          if (v == 'delete') {
                            onDelete();
                          } else if (v == 'whatsapp_bill') {
                            await onSendWhatsAppBill?.call(force: true);
                          } else if (v == 'whatsapp_bill_resend') {
                            await onSendWhatsAppBill?.call(force: true);
                          } else {
                            onUpdateStatus(v);
                          }
                        },
                        child: const Icon(Icons.more_vert_rounded,
                            color: AppTheme.textMuted, size: 20),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Items summary
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 10, 14, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ...order.items.take(isTablet ? 2 : 3).map(
                        (item) => Padding(
                          padding: const EdgeInsets.only(bottom: 3),
                          child: Row(
                            children: [
                              Text('${item.quantity}×',
                                  style: GoogleFonts.poppins(
                                    fontSize: 12,
                                    color: AppTheme.primary,
                                    fontWeight: FontWeight.w600,
                                  )),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  item.name,
                                  style: GoogleFonts.poppins(
                                    fontSize: 12,
                                    color: AppTheme.textSecondary,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              Text(
                                'TZS ${NumberFormat('#,##0').format(item.total)}',
                                style: GoogleFonts.poppins(
                                  fontSize: 11,
                                  color: AppTheme.textMuted,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  if (order.items.length > (isTablet ? 2 : 3))
                    Text(
                      '+${order.items.length - (isTablet ? 2 : 3)} items zaidi',
                      style: GoogleFonts.poppins(
                          fontSize: 11, color: AppTheme.textMuted),
                    ),
                ],
              ),
            ),

            // Footer
            Container(
              padding: const EdgeInsets.fromLTRB(14, 8, 14, 12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: AppTheme.border)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.restaurant_menu_rounded,
                          size: 13, color: AppTheme.textMuted),
                      const SizedBox(width: 4),
                      Text(
                        '${order.items.length} items',
                        style: GoogleFonts.poppins(
                          fontSize: 12,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                  Text(
                    'TZS ${currency.format(order.totalAmount)}',
                    style: GoogleFonts.poppins(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _menuItem(IconData icon, String label, Color color) {
    return Row(
      children: [
        Icon(icon, color: color, size: 18),
        const SizedBox(width: 10),
        Text(label,
            style:
                GoogleFonts.poppins(fontSize: 13, color: AppTheme.textPrimary)),
      ],
    );
  }
}
