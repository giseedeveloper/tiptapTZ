<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Customer wait-time and waiter speed analytics from order workflow timestamps.
 *
 * Definitions:
 * - wait_to_ready: received/created → ready (kitchen turnaround)
 * - wait_to_served: received/created → served (true customer wait)
 * - cycle: received/created → completed (full order lifecycle)
 */
class WaitTimeAnalyticsService
{
    public function __construct(private OrderWorkflowService $workflow)
    {
    }

    /**
     * @return array{
     *     avg_to_ready_minutes: float|null,
     *     avg_to_served_minutes: float|null,
     *     avg_cycle_minutes: float|null,
     *     median_to_served_minutes: float|null,
     *     sample_to_ready: int,
     *     sample_to_served: int,
     *     sample_cycle: int,
     *     stage_times: list<array{key: string, label: string, avg_minutes: float, sample_size: int, threshold_minutes: int, is_bottleneck: bool}>,
     *     bottlenecks: list<array{key: string, label: string, avg_minutes: float, threshold_minutes: int, severity: string}>
     * }
     */
    public function summarize(int $restaurantId, Carbon $from, Carbon $to): array
    {
        $orders = $this->ordersInRange($restaurantId, $from, $to);

        $toReady = [];
        $toServed = [];
        $cycle = [];

        foreach ($orders as $order) {
            $start = $this->startAt($order);
            if (! $start) {
                continue;
            }

            if ($order->ready_at && $order->ready_at->greaterThan($start)) {
                $toReady[] = $start->diffInSeconds($order->ready_at) / 60;
            }
            if ($order->served_at && $order->served_at->greaterThan($start)) {
                $toServed[] = $start->diffInSeconds($order->served_at) / 60;
            }
            if ($order->completed_at && $order->completed_at->greaterThan($start)) {
                $cycle[] = $start->diffInSeconds($order->completed_at) / 60;
            }
        }

        $stageTimesRaw = $this->workflow->averageStageDurations($restaurantId, $from, $to);
        $thresholds = OrderWorkflow::bottleneckThresholds();
        $stageTimes = [];
        $bottlenecks = [];

        foreach (OrderWorkflow::liveStatuses() as $status) {
            $avg = (float) ($stageTimesRaw[$status]['avg_minutes'] ?? 0);
            $sample = (int) ($stageTimesRaw[$status]['sample_size'] ?? 0);
            $threshold = (int) ($thresholds[$status] ?? 15);
            $isBottleneck = $sample >= 3 && $avg > $threshold;

            $row = [
                'key' => $status,
                'label' => OrderWorkflow::label($status),
                'avg_minutes' => round($avg, 1),
                'sample_size' => $sample,
                'threshold_minutes' => $threshold,
                'is_bottleneck' => $isBottleneck,
            ];
            $stageTimes[] = $row;

            if ($isBottleneck) {
                $bottlenecks[] = [
                    'key' => $status,
                    'label' => OrderWorkflow::label($status),
                    'avg_minutes' => round($avg, 1),
                    'threshold_minutes' => $threshold,
                    'severity' => $avg >= ($threshold * 2) ? 'critical' : 'warning',
                ];
            }
        }

        usort($bottlenecks, fn ($a, $b) => $b['avg_minutes'] <=> $a['avg_minutes']);

        return [
            'avg_to_ready_minutes' => $this->avgOrNull($toReady),
            'avg_to_served_minutes' => $this->avgOrNull($toServed),
            'avg_cycle_minutes' => $this->avgOrNull($cycle),
            'median_to_served_minutes' => $this->medianOrNull($toServed),
            'sample_to_ready' => count($toReady),
            'sample_to_served' => count($toServed),
            'sample_cycle' => count($cycle),
            'stage_times' => $stageTimes,
            'bottlenecks' => $bottlenecks,
        ];
    }

    /**
     * @return list<array{date: string, label: string, avg_to_served_minutes: float|null, avg_to_ready_minutes: float|null, sample_served: int, orders: int}>
     */
    public function dailyTrend(int $restaurantId, Carbon $from, Carbon $to): array
    {
        $orders = $this->ordersInRange($restaurantId, $from, $to);
        $byDay = $orders->groupBy(fn (Order $o) => $o->created_at->toDateString());

        $trend = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $dayOrders = $byDay->get($key, collect());

            $toReady = [];
            $toServed = [];
            foreach ($dayOrders as $order) {
                $start = $this->startAt($order);
                if (! $start) {
                    continue;
                }
                if ($order->ready_at && $order->ready_at->greaterThan($start)) {
                    $toReady[] = $start->diffInSeconds($order->ready_at) / 60;
                }
                if ($order->served_at && $order->served_at->greaterThan($start)) {
                    $toServed[] = $start->diffInSeconds($order->served_at) / 60;
                }
            }

            $trend[] = [
                'date' => $key,
                'label' => $cursor->format('D d'),
                'avg_to_served_minutes' => $this->avgOrNull($toServed),
                'avg_to_ready_minutes' => $this->avgOrNull($toReady),
                'sample_served' => count($toServed),
                'orders' => $dayOrders->count(),
            ];

            $cursor->addDay();
        }

        return $trend;
    }

    /**
     * Per-waiter speed / customer wait metrics for orders assigned to them.
     *
     * @return list<array{
     *     waiter_id: int,
     *     name: string,
     *     orders_count: int,
     *     avg_to_ready_minutes: float|null,
     *     avg_to_served_minutes: float|null,
     *     sample_to_ready: int,
     *     sample_to_served: int
     * }>
     */
    public function waiterSpeedMetrics(int $restaurantId, Carbon $from, Carbon $to): array
    {
        $orders = $this->ordersInRange($restaurantId, $from, $to)
            ->filter(fn (Order $o) => filled($o->waiter_id));

        $waiterIds = $orders->pluck('waiter_id')->unique()->values();
        $names = User::query()
            ->whereIn('id', $waiterIds)
            ->pluck('name', 'id');

        return $orders
            ->groupBy('waiter_id')
            ->map(function (Collection $rows, $waiterId) use ($names) {
                $toReady = [];
                $toServed = [];

                foreach ($rows as $order) {
                    $start = $this->startAt($order);
                    if (! $start) {
                        continue;
                    }
                    if ($order->ready_at && $order->ready_at->greaterThan($start)) {
                        $toReady[] = $start->diffInSeconds($order->ready_at) / 60;
                    }
                    if ($order->served_at && $order->served_at->greaterThan($start)) {
                        $toServed[] = $start->diffInSeconds($order->served_at) / 60;
                    }
                }

                return [
                    'waiter_id' => (int) $waiterId,
                    'name' => (string) ($names[$waiterId] ?? 'Waiter #'.$waiterId),
                    'orders_count' => $rows->count(),
                    'avg_to_ready_minutes' => $this->avgOrNull($toReady),
                    'avg_to_served_minutes' => $this->avgOrNull($toServed),
                    'sample_to_ready' => count($toReady),
                    'sample_to_served' => count($toServed),
                ];
            })
            ->sortBy(fn (array $row) => $row['avg_to_served_minutes'] ?? PHP_FLOAT_MAX)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Order>
     */
    private function ordersInRange(int $restaurantId, Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', OrderWorkflow::storageVariants(OrderWorkflow::CANCELLED))
            ->get([
                'id',
                'waiter_id',
                'status',
                'created_at',
                'received_at',
                'accepted_at',
                'preparing_at',
                'ready_at',
                'served_at',
                'completed_at',
            ]);
    }

    private function startAt(Order $order): ?Carbon
    {
        return $order->received_at ?? $order->created_at;
    }

    /**
     * @param  list<float>  $values
     */
    private function avgOrNull(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        return round(array_sum($values) / count($values), 1);
    }

    /**
     * @param  list<float>  $values
     */
    private function medianOrNull(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        sort($values);
        $count = count($values);
        $mid = intdiv($count, 2);

        if ($count % 2 === 1) {
            return round($values[$mid], 1);
        }

        return round(($values[$mid - 1] + $values[$mid]) / 2, 1);
    }
}
