<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Report — {{ $metrics['report_date'] ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; margin: 0; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 22px 0 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .muted { color: #666; margin-bottom: 16px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .grid td, .grid th { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .grid th { background: #f3f3f3; font-size: 11px; text-transform: uppercase; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 0 -8px 12px; }
        .card { background: #f7f7fb; border: 1px solid #e5e5ef; border-radius: 6px; padding: 10px; }
        .card .label { font-size: 10px; text-transform: uppercase; color: #666; }
        .card .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    @php
        $symbol = $metrics['currency_symbol'] ?? 'R';
        $peak = $metrics['peak_hour'] ?? null;
    @endphp

    <h1>{{ $restaurant->name }} — Daily Report</h1>
    <p class="muted">
        Date: {{ \Illuminate\Support\Carbon::parse($metrics['report_date'])->format('D, d M Y') }}
        · Generated: {{ now()->format('d M Y H:i') }}
    </p>

    <table class="cards">
        <tr>
            <td class="card" width="25%"><div class="label">Orders</div><div class="value">{{ number_format($metrics['orders']['total'] ?? 0) }}</div></td>
            <td class="card" width="25%"><div class="label">Revenue</div><div class="value">{{ $symbol }} {{ number_format($metrics['revenue']['total'] ?? 0, 2) }}</div></td>
            <td class="card" width="25%"><div class="label">AOV</div><div class="value">{{ $symbol }} {{ number_format($metrics['aov'] ?? 0, 2) }}</div></td>
            <td class="card" width="25%"><div class="label">Customers</div><div class="value">{{ number_format($metrics['customers']['unique'] ?? 0) }}</div></td>
        </tr>
        <tr>
            <td class="card"><div class="label">Returning</div><div class="value">{{ number_format($metrics['customers']['returning'] ?? 0) }}</div></td>
            <td class="card"><div class="label">New</div><div class="value">{{ number_format($metrics['customers']['new'] ?? 0) }}</div></td>
            <td class="card"><div class="label">Turnover rate</div><div class="value">{{ number_format($metrics['turnover']['rate'] ?? 0, 2) }}x</div></td>
            <td class="card"><div class="label">Peak hour</div><div class="value">{{ $peak['label'] ?? '—' }} ({{ $peak['orders'] ?? 0 }})</div></td>
        </tr>
    </table>

    <h2>Turnover</h2>
    <table class="grid">
        <tr>
            <th>Completed turns</th>
            <th>Tables used</th>
            <th>Active tables</th>
            <th>Rate</th>
        </tr>
        <tr>
            <td>{{ $metrics['turnover']['completed_turns'] ?? 0 }}</td>
            <td>{{ $metrics['turnover']['tables_used'] ?? 0 }}</td>
            <td>{{ $metrics['turnover']['active_tables'] ?? 0 }}</td>
            <td>{{ number_format($metrics['turnover']['rate'] ?? 0, 2) }}x</td>
        </tr>
    </table>

    <h2>Top items</h2>
    <table class="grid">
        <thead>
            <tr><th>Item</th><th class="right">Qty</th><th class="right">Revenue</th></tr>
        </thead>
        <tbody>
            @forelse(($metrics['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="right">{{ $item['quantity'] }}</td>
                    <td class="right">{{ $symbol }} {{ number_format($item['revenue'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No items sold</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Customer wait-time</h2>
    @php $wt = $metrics['wait_time'] ?? []; @endphp
    <table class="grid">
        <tbody>
            <tr><td>Avg wait to ready</td><td class="right">{{ ($wt['avg_to_ready_minutes'] ?? null) !== null ? number_format($wt['avg_to_ready_minutes'], 1).' min' : '—' }}</td></tr>
            <tr><td>Avg customer wait to served</td><td class="right">{{ ($wt['avg_to_served_minutes'] ?? null) !== null ? number_format($wt['avg_to_served_minutes'], 1).' min' : '—' }}</td></tr>
            <tr><td>Median wait to served</td><td class="right">{{ ($wt['median_to_served_minutes'] ?? null) !== null ? number_format($wt['median_to_served_minutes'], 1).' min' : '—' }}</td></tr>
            <tr><td>Avg full cycle</td><td class="right">{{ ($wt['avg_cycle_minutes'] ?? null) !== null ? number_format($wt['avg_cycle_minutes'], 1).' min' : '—' }}</td></tr>
        </tbody>
    </table>

    <h2>Waiter performance</h2>
    <table class="grid">
        <thead>
            <tr><th>Waiter</th><th class="right">Orders</th><th class="right">Revenue</th><th class="right">Tips</th><th class="right">Avg wait</th></tr>
        </thead>
        <tbody>
            @forelse(($metrics['waiter_performance'] ?? []) as $waiter)
                <tr>
                    <td>{{ $waiter['name'] }}</td>
                    <td class="right">{{ $waiter['orders'] }}</td>
                    <td class="right">{{ $symbol }} {{ number_format($waiter['revenue'], 2) }}</td>
                    <td class="right">{{ $symbol }} {{ number_format($waiter['tips'], 2) }}</td>
                    <td class="right">{{ ($waiter['avg_to_served_minutes'] ?? null) !== null ? number_format($waiter['avg_to_served_minutes'], 1).'m' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No waiter activity</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Peak hours</h2>
    <table class="grid">
        <thead>
            <tr><th>Hour</th><th class="right">Orders</th></tr>
        </thead>
        <tbody>
            @foreach(collect($metrics['peak_hours'] ?? [])->filter(fn ($h) => ($h['orders'] ?? 0) > 0)->values() as $hour)
                <tr>
                    <td>{{ $hour['label'] }}</td>
                    <td class="right">{{ $hour['orders'] }}</td>
                </tr>
            @endforeach
            @if(collect($metrics['peak_hours'] ?? [])->sum('orders') === 0)
                <tr><td colspan="2">No hourly activity</td></tr>
            @endif
        </tbody>
    </table>
</body>
</html>
