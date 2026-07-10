import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/roster_model.dart';
import 'glass_card.dart';

/// Waiter roster: assigned tables, shifts, absent status, manager updates.
class RosterDashboardSection extends StatelessWidget {
  final List<AssignedTable> myTables;
  final List<WaiterShiftInfo> todayShifts;
  final List<RosterNotification> notifications;
  final bool isAbsentToday;
  final VoidCallback? onDismissNotifications;
  final bool isDismissing;

  const RosterDashboardSection({
    super.key,
    required this.myTables,
    required this.todayShifts,
    required this.notifications,
    required this.isAbsentToday,
    this.onDismissNotifications,
    this.isDismissing = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (isAbsentToday) ...[
          _AbsentBanner(),
          const SizedBox(height: 16),
        ],
        if (notifications.isNotEmpty) ...[
          _NotificationsCard(
            notifications: notifications,
            onDismiss: onDismissNotifications,
            isDismissing: isDismissing,
          ),
          const SizedBox(height: 16),
        ],
        _MyTablesCard(tables: myTables, shifts: todayShifts),
      ],
    );
  }
}

class _AbsentBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        gradient: LinearGradient(
          colors: [
            const Color(0xFFBE123C).withValues(alpha: 0.25),
            const Color(0xFF9F1239).withValues(alpha: 0.12),
          ],
        ),
        border: Border.all(color: const Color(0xFFF43F5E).withValues(alpha: 0.35)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF43F5E).withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.event_busy_rounded, color: Color(0xFFFDA4AF), size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Marked absent today',
                  style: GoogleFonts.poppins(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFFFECDD3),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Your manager marked you absent. You will not receive new assignments until cleared.',
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: const Color(0xFFFECDD3).withValues(alpha: 0.75),
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _NotificationsCard extends StatelessWidget {
  final List<RosterNotification> notifications;
  final VoidCallback? onDismiss;
  final bool isDismissing;

  const _NotificationsCard({
    required this.notifications,
    this.onDismiss,
    this.isDismissing = false,
  });

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      tint: const Color(0xFF14B8A6).withValues(alpha: 0.12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF14B8A6).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.campaign_rounded, color: Color(0xFF5EEAD4), size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Roster Updates',
                  style: GoogleFonts.poppins(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
              if (onDismiss != null)
                TextButton(
                  onPressed: isDismissing ? null : onDismiss,
                  child: isDismissing
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF5EEAD4)),
                        )
                      : Text(
                          'Mark read',
                          style: GoogleFonts.poppins(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF5EEAD4),
                          ),
                        ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          ...notifications.take(3).map((n) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF14B8A6).withValues(alpha: 0.2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        n.message,
                        style: GoogleFonts.poppins(
                          fontSize: 12,
                          color: const Color(0xFFCCFBF1),
                          height: 1.4,
                        ),
                      ),
                      if (n.assignedBy != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          'From manager: ${n.assignedBy}',
                          style: GoogleFonts.poppins(
                            fontSize: 10,
                            color: Colors.white.withValues(alpha: 0.4),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              )),
        ],
      ),
    );
  }
}

class _MyTablesCard extends StatelessWidget {
  final List<AssignedTable> tables;
  final List<WaiterShiftInfo> shifts;

  const _MyTablesCard({required this.tables, required this.shifts});

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(18),
      tint: const Color(0xFF8B5CF6).withValues(alpha: 0.1),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.table_restaurant_rounded, color: Color(0xFFA78BFA), size: 20),
                        const SizedBox(width: 8),
                        Text(
                          'My Tables',
                          style: GoogleFonts.poppins(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Tables assigned to you by your manager',
                      style: GoogleFonts.poppins(
                        fontSize: 11,
                        color: Colors.white.withValues(alpha: 0.45),
                      ),
                    ),
                  ],
                ),
              ),
              if (shifts.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF14B8A6).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF14B8A6).withValues(alpha: 0.25)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'SHIFT TODAY',
                        style: GoogleFonts.poppins(
                          fontSize: 8,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.8,
                          color: const Color(0xFF5EEAD4),
                        ),
                      ),
                      ...shifts.map(
                        (s) => Text(
                          s.label != null && s.label!.isNotEmpty
                              ? '${s.timeRange} · ${s.label}'
                              : s.timeRange,
                          style: GoogleFonts.poppins(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),
          if (tables.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.08),
                  style: BorderStyle.solid,
                ),
                color: Colors.white.withValues(alpha: 0.03),
              ),
              child: Column(
                children: [
                  Icon(Icons.event_seat_outlined, color: Colors.white.withValues(alpha: 0.25), size: 32),
                  const SizedBox(height: 8),
                  Text(
                    'No tables assigned yet',
                    style: GoogleFonts.poppins(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Colors.white.withValues(alpha: 0.5),
                    ),
                  ),
                  Text(
                    'Your manager will assign tables from Waiter Roster',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      color: Colors.white.withValues(alpha: 0.35),
                    ),
                  ),
                ],
              ),
            )
          else
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: tables.map((t) {
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        const Color(0xFF7C3AED).withValues(alpha: 0.35),
                        const Color(0xFF06B6D4).withValues(alpha: 0.2),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF8B5CF6).withValues(alpha: 0.35)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        t.name,
                        style: GoogleFonts.poppins(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFFDDD6FE),
                        ),
                      ),
                      if (t.zone != null && t.zone!.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            t.zone!.toUpperCase(),
                            style: GoogleFonts.poppins(
                              fontSize: 8,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.5,
                              color: Colors.white.withValues(alpha: 0.55),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              }).toList(),
            ),
        ],
      ),
    );
  }
}
