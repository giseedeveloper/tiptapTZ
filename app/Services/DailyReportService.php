<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Tip;
use App\Models\User;
use App\Support\Money;
use App\Support\OrderWorkflow;
use App\Support\SimpleXlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DailyReportService
{
    public function __construct(
        private readonly SimpleXlsxWriter $xlsxWriter,
        private readonly WaitTimeAnalyticsService $waitTimes,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildMetrics(Restaurant $restaurant, Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        $restaurantId = (int) $restaurant->id;

        $orders = Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->get(['id', 'waiter_id', 'table_number', 'customer_phone', 'whatsapp_jid', 'status', 'total_amount', 'created_at']);

        $orderIds = $orders->pluck('id');

        $revenue = (float) Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $ordersTotal = $orders->count();
        $aov = $ordersTotal > 0 ? round($revenue / $ordersTotal, 2) : 0.0;

        $customerKeys = $orders
            ->map(fn (Order $order) => $this->customerKey($order))
            ->filter()
            ->unique()
            ->values();

        $returningCustomers = 0;
        if ($customerKeys->isNotEmpty()) {
            $previousCustomerKeys = Order::withoutGlobalScopes()
                ->where('restaurant_id', $restaurantId)
                ->where('created_at', '<', $start)
                ->get(['customer_phone', 'whatsapp_jid'])
                ->map(fn (Order $order) => $this->customerKey($order))
                ->filter()
                ->unique();

            $returningCustomers = $customerKeys->intersect($previousCustomerKeys)->count();
        }

        $items = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('COALESCE(name, ?) as item_name', ['Item'])
            ->selectRaw('SUM(quantity) as quantity')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('item_name')
            ->orderByDesc('quantity')
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->item_name,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->values()
            ->all();

        $tipsByWaiter = Tip::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->get(['waiter_id', 'amount'])
            ->groupBy('waiter_id')
            ->map(fn (Collection $rows) => round((float) $rows->sum('amount'), 2));

        $waiterIds = $orders->pluck('waiter_id')->filter()->unique()->values();
        $waiters = User::query()
            ->whereIn('id', $waiterIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $speedByWaiter = collect($this->waitTimes->waiterSpeedMetrics($restaurantId, $start, $end))
            ->keyBy('waiter_id');

        $waiterPerformance = $orders
            ->filter(fn (Order $order) => $order->waiter_id)
            ->groupBy('waiter_id')
            ->map(function (Collection $rows, $waiterId) use ($waiters, $tipsByWaiter, $speedByWaiter) {
                $waiter = $waiters->get($waiterId);
                $speed = $speedByWaiter->get($waiterId);

                return [
                    'waiter_id' => (int) $waiterId,
                    'name' => $waiter?->name ?? 'Waiter #'.$waiterId,
                    'orders' => $rows->count(),
                    'revenue' => round((float) $rows->sum('total_amount'), 2),
                    'tips' => (float) ($tipsByWaiter[$waiterId] ?? 0),
                    'avg_to_ready_minutes' => $speed['avg_to_ready_minutes'] ?? null,
                    'avg_to_served_minutes' => $speed['avg_to_served_minutes'] ?? null,
                ];
            })
            ->sortByDesc('orders')
            ->values()
            ->all();

        $waitTime = $this->waitTimes->summarize($restaurantId, $start, $end);

        $activeTables = Table::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->count();

        $paidOrderIds = Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->filter()
            ->unique()
            ->values();

        $completedTurns = $orders
            ->filter(fn (Order $order) => filled($order->table_number))
            ->filter(fn (Order $order) => in_array(OrderWorkflow::normalize($order->status), [OrderWorkflow::SERVED, OrderWorkflow::COMPLETED], true)
                || $paidOrderIds->contains($order->id))
            ->count();

        $tablesUsed = $orders
            ->pluck('table_number')
            ->filter(fn ($table) => filled($table))
            ->unique()
            ->count();

        $turnoverRate = $activeTables > 0
            ? round($completedTurns / $activeTables, 2)
            : (float) $completedTurns;

        $hourly = [];
        for ($h = 0; $h <= 23; $h++) {
            $hourly[$h] = [
                'hour' => $h,
                'label' => sprintf('%02d:00', $h),
                'orders' => 0,
            ];
        }

        foreach ($orders as $order) {
            $hour = (int) $order->created_at->format('G');
            $hourly[$hour]['orders']++;
        }

        $peakHours = array_values($hourly);
        $peakHour = collect($peakHours)
            ->sortBy([
                ['orders', 'desc'],
                ['hour', 'desc'],
            ])
            ->first();
        if (! $peakHour || (int) $peakHour['orders'] === 0) {
            $peakHour = null;
        }

        $byStatus = $orders
            ->groupBy(fn (Order $order) => OrderWorkflow::normalize($order->status))
            ->map(fn (Collection $rows) => $rows->count())
            ->all();

        return [
            'restaurant' => [
                'id' => $restaurantId,
                'name' => $restaurant->name,
            ],
            'report_date' => $date->toDateString(),
            'currency_symbol' => Money::symbol(),
            'currency_code' => (string) config('tiptap.currency_code', 'TZS'),
            'orders' => [
                'total' => $ordersTotal,
                'by_status' => $byStatus,
            ],
            'revenue' => [
                'total' => round($revenue, 2),
            ],
            'aov' => $aov,
            'customers' => [
                'unique' => $customerKeys->count(),
                'returning' => $returningCustomers,
                'new' => max(0, $customerKeys->count() - $returningCustomers),
            ],
            'items' => $items,
            'waiter_performance' => $waiterPerformance,
            'wait_time' => [
                'avg_to_ready_minutes' => $waitTime['avg_to_ready_minutes'],
                'avg_to_served_minutes' => $waitTime['avg_to_served_minutes'],
                'avg_cycle_minutes' => $waitTime['avg_cycle_minutes'],
                'median_to_served_minutes' => $waitTime['median_to_served_minutes'],
                'sample_to_ready' => $waitTime['sample_to_ready'],
                'sample_to_served' => $waitTime['sample_to_served'],
                'sample_cycle' => $waitTime['sample_cycle'],
                'bottlenecks' => $waitTime['bottlenecks'],
            ],
            'turnover' => [
                'completed_turns' => $completedTurns,
                'tables_used' => $tablesUsed,
                'active_tables' => $activeTables,
                'rate' => $turnoverRate,
            ],
            'peak_hours' => $peakHours,
            'peak_hour' => $peakHour,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function generate(
        Restaurant $restaurant,
        Carbon $date,
        string $source = DailyReport::SOURCE_MANUAL,
        bool $force = false,
    ): DailyReport {
        $existing = DailyReport::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('report_date', $date)
            ->first();

        if ($existing && ! $force && $existing->hasPdf() && $existing->hasExcel()) {
            return $existing;
        }

        $metrics = $this->buildMetrics($restaurant, $date);
        $paths = $this->storeExportFiles($restaurant, $date, $metrics);

        return DailyReport::query()->updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'report_date' => $date->toDateString(),
            ],
            [
                'metrics' => $metrics,
                'pdf_path' => $paths['pdf'],
                'excel_path' => $paths['excel'],
                'generated_at' => now(),
                'generation_source' => $source,
            ],
        );
    }

    /**
     * @return array{generated: int, skipped: int}
     */
    public function generateForAllActiveRestaurants(?Carbon $date = null, bool $force = false): array
    {
        $date ??= now()->subDay()->startOfDay();
        $generated = 0;
        $skipped = 0;

        Restaurant::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->chunkById(50, function ($restaurants) use ($date, $force, &$generated, &$skipped): void {
                foreach ($restaurants as $restaurant) {
                    $existing = DailyReport::query()
                        ->where('restaurant_id', $restaurant->id)
                        ->whereDate('report_date', $date)
                        ->first();

                    if ($existing && ! $force && $existing->hasPdf() && $existing->hasExcel()) {
                        $skipped++;

                        continue;
                    }

                    $this->generate($restaurant, $date, DailyReport::SOURCE_SCHEDULED, $force);
                    $generated++;
                }
            });

        return compact('generated', 'skipped');
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return array{pdf: string, excel: string}
     */
    public function storeExportFiles(Restaurant $restaurant, Carbon $date, array $metrics): array
    {
        $baseDir = 'daily-reports/'.$restaurant->id;
        $stamp = $date->format('Y-m-d');
        $pdfRelative = $baseDir.'/'.$stamp.'.pdf';
        $excelRelative = $baseDir.'/'.$stamp.'.xlsx';

        $pdfBinary = Pdf::loadView('reports.daily-pdf', [
            'metrics' => $metrics,
            'restaurant' => $restaurant,
            'reportDate' => $date,
        ])->setPaper('a4')->output();

        Storage::disk('local')->put($pdfRelative, $pdfBinary);

        $excelAbsolute = Storage::disk('local')->path($excelRelative);
        $this->xlsxWriter->write($excelAbsolute, $this->excelSheets($metrics));

        if (! is_file($excelAbsolute)) {
            throw new RuntimeException('Failed to write Excel daily report.');
        }

        return [
            'pdf' => $pdfRelative,
            'excel' => $excelRelative,
        ];
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return list<array{title: string, rows: list<list<string|int|float|null>>}>
     */
    public function excelSheets(array $metrics): array
    {
        $symbol = (string) ($metrics['currency_symbol'] ?? Money::symbol());

        $summary = [
            ['Metric', 'Value'],
            ['Restaurant', $metrics['restaurant']['name'] ?? ''],
            ['Report date', $metrics['report_date'] ?? ''],
            ['Orders', $metrics['orders']['total'] ?? 0],
            ['Revenue ('.$symbol.')', $metrics['revenue']['total'] ?? 0],
            ['AOV ('.$symbol.')', $metrics['aov'] ?? 0],
            ['Unique customers', $metrics['customers']['unique'] ?? 0],
            ['Returning customers', $metrics['customers']['returning'] ?? 0],
            ['New customers', $metrics['customers']['new'] ?? 0],
            ['Table turnover rate', $metrics['turnover']['rate'] ?? 0],
            ['Completed turns', $metrics['turnover']['completed_turns'] ?? 0],
            ['Tables used', $metrics['turnover']['tables_used'] ?? 0],
            ['Active tables', $metrics['turnover']['active_tables'] ?? 0],
            ['Peak hour', $metrics['peak_hour']['label'] ?? '—'],
            ['Peak hour orders', $metrics['peak_hour']['orders'] ?? 0],
        ];

        $items = [['Item', 'Quantity', 'Revenue ('.$symbol.')']];
        foreach ($metrics['items'] ?? [] as $item) {
            $items[] = [$item['name'] ?? '', $item['quantity'] ?? 0, $item['revenue'] ?? 0];
        }

        $waiters = [['Waiter', 'Orders', 'Revenue ('.$symbol.')', 'Tips ('.$symbol.')', 'Avg Ready (min)', 'Avg Served Wait (min)']];
        foreach ($metrics['waiter_performance'] ?? [] as $waiter) {
            $waiters[] = [
                $waiter['name'] ?? '',
                $waiter['orders'] ?? 0,
                $waiter['revenue'] ?? 0,
                $waiter['tips'] ?? 0,
                $waiter['avg_to_ready_minutes'] ?? '',
                $waiter['avg_to_served_minutes'] ?? '',
            ];
        }

        $waitSummary = [
            ['Metric', 'Value'],
            ['Avg wait to ready (min)', $metrics['wait_time']['avg_to_ready_minutes'] ?? ''],
            ['Avg customer wait to served (min)', $metrics['wait_time']['avg_to_served_minutes'] ?? ''],
            ['Median customer wait (min)', $metrics['wait_time']['median_to_served_minutes'] ?? ''],
            ['Avg full cycle (min)', $metrics['wait_time']['avg_cycle_minutes'] ?? ''],
            ['Samples (served)', $metrics['wait_time']['sample_to_served'] ?? 0],
        ];

        $peaks = [['Hour', 'Orders']];
        foreach ($metrics['peak_hours'] ?? [] as $hour) {
            $peaks[] = [$hour['label'] ?? '', $hour['orders'] ?? 0];
        }

        return [
            ['title' => 'Summary', 'rows' => $summary],
            ['title' => 'Wait Time', 'rows' => $waitSummary],
            ['title' => 'Items', 'rows' => $items],
            ['title' => 'Waiters', 'rows' => $waiters],
            ['title' => 'Peak Hours', 'rows' => $peaks],
        ];
    }

    public function absolutePath(DailyReport $report, string $format): string
    {
        $relative = $format === 'pdf' ? $report->pdf_path : $report->excel_path;
        if (! filled($relative) || ! Storage::disk('local')->exists($relative)) {
            throw new RuntimeException(strtoupper($format).' report file not found.');
        }

        return Storage::disk('local')->path($relative);
    }

    private function customerKey(Order $order): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $order->customer_phone);
        if ($phone !== '') {
            return 'p:'.$phone;
        }

        $jid = trim((string) $order->whatsapp_jid);
        if ($jid !== '') {
            return 'j:'.strtolower($jid);
        }

        return null;
    }
}
