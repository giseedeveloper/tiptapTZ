class AssignedTable {
  final int id;
  final String name;
  final String? zone;

  const AssignedTable({
    required this.id,
    required this.name,
    this.zone,
  });

  factory AssignedTable.fromJson(Map<String, dynamic> json) {
    return AssignedTable(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'Table',
      zone: json['zone'] as String?,
    );
  }
}

class WaiterShiftInfo {
  final int id;
  final String startsAt;
  final String endsAt;
  final String? label;

  const WaiterShiftInfo({
    required this.id,
    required this.startsAt,
    required this.endsAt,
    this.label,
  });

  String get timeRange => '$startsAt – $endsAt';

  factory WaiterShiftInfo.fromJson(Map<String, dynamic> json) {
    return WaiterShiftInfo(
      id: json['id'] as int,
      startsAt: json['starts_at'] as String? ?? '',
      endsAt: json['ends_at'] as String? ?? '',
      label: json['label'] as String?,
    );
  }
}

class RosterNotification {
  final String id;
  final String message;
  final List<String> tableNames;
  final String? assignedBy;
  final String createdAt;

  const RosterNotification({
    required this.id,
    required this.message,
    required this.tableNames,
    this.assignedBy,
    required this.createdAt,
  });

  factory RosterNotification.fromJson(Map<String, dynamic> json) {
    final tables = json['table_names'];
    return RosterNotification(
      id: '${json['id']}',
      message: json['message'] as String? ?? '',
      tableNames: tables is List
          ? tables.map((e) => e.toString()).toList()
          : const [],
      assignedBy: json['assigned_by'] as String?,
      createdAt: json['created_at'] as String? ?? '',
    );
  }
}
