<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusEvent;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderReadyNotification;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    /**
     * Advance or set order status with timestamps, event history, and side effects.
     *
     * @param  array<string, mixed>  $meta
     */
    public function transition(
        Order $order,
        string $toStatus,
        ?User $actor = null,
        ?string $source = null,
        array $meta = [],
        bool $ensurePaymentOnComplete = false,
    ): Order {
        $to = OrderWorkflow::normalize($toStatus);
        $from = OrderWorkflow::normalize($order->status);

        if ($from === $to) {
            return $order;
        }

        if (! OrderWorkflow::canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move order from {$from} to {$to}.",
            ]);
        }

        return DB::transaction(function () use ($order, $from, $to, $actor, $source, $meta, $ensurePaymentOnComplete) {
            $order->refresh();

            $durationSeconds = null;
            $lastEvent = OrderStatusEvent::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->first();

            if ($lastEvent) {
                $durationSeconds = max(0, $lastEvent->created_at->diffInSeconds(now()));
            } elseif ($order->created_at) {
                $durationSeconds = max(0, $order->created_at->diffInSeconds(now()));
            }

            $payload = ['status' => $to];
            $now = now();

            // Set timestamps for every stage up to (and including) the target
            // so skipped stages still get a clock for bottleneck metrics.
            $toIndex = array_search($to, OrderWorkflow::PIPELINE, true);
            if ($toIndex !== false) {
                foreach (OrderWorkflow::PIPELINE as $index => $stage) {
                    if ($index > $toIndex) {
                        break;
                    }
                    $column = OrderWorkflow::timestampColumn($stage);
                    if ($column && empty($order->{$column})) {
                        $payload[$column] = $now;
                    }
                }
            }

            if ($to === OrderWorkflow::CANCELLED) {
                // no timestamp columns for cancel
            }

            $order->update($payload);

            OrderStatusEvent::create([
                'order_id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $actor?->id,
                'source' => $source,
                'duration_seconds' => $durationSeconds,
                'meta' => $meta ?: null,
            ]);

            if ($to === OrderWorkflow::SERVED && $from !== OrderWorkflow::SERVED) {
                OrderBillNotification::maybePushBillImage($order);
            }

            if ($to === OrderWorkflow::READY && $order->waiter_id) {
                $waiter = $order->waiter;
                if ($waiter) {
                    $waiter->notify(new OrderReadyNotification($order));
                }
            }

            if ($to === OrderWorkflow::COMPLETED && $ensurePaymentOnComplete && ! $order->payments()->exists()) {
                Payment::create([
                    'order_id' => $order->id,
                    'restaurant_id' => $order->restaurant_id,
                    'waiter_id' => $order->waiter_id,
                    'customer_phone' => $order->customer_phone,
                    'amount' => $order->total_amount,
                    'method' => 'manual',
                    'status' => 'paid',
                    'description' => 'Marked completed from order workflow',
                ]);
            }

            return $order->fresh(['items.menuItem', 'waiter', 'payments']);
        });
    }

    /**
     * Mark order as received on create (idempotent).
     */
    public function markReceived(Order $order, ?User $actor = null, ?string $source = null): Order
    {
        if (empty($order->received_at)) {
            $order->forceFill([
                'status' => OrderWorkflow::RECEIVED,
                'received_at' => $order->created_at ?? now(),
            ])->saveQuietly();
        }

        $exists = OrderStatusEvent::query()
            ->where('order_id', $order->id)
            ->where('to_status', OrderWorkflow::RECEIVED)
            ->exists();

        if (! $exists) {
            OrderStatusEvent::create([
                'order_id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'from_status' => null,
                'to_status' => OrderWorkflow::RECEIVED,
                'changed_by' => $actor?->id,
                'source' => $source ?? 'create',
                'duration_seconds' => 0,
                'meta' => null,
            ]);
        }

        return $order->fresh();
    }

    /**
     * Complete order when payment succeeds (payment record stays on payments table).
     */
    public function completeFromPayment(Order $order, ?string $source = 'payment'): Order
    {
        if (OrderWorkflow::isTerminal($order->status)) {
            if (empty($order->completed_at)) {
                $order->forceFill(['completed_at' => now()])->saveQuietly();
            }

            return $order;
        }

        return $this->transition($order, OrderWorkflow::COMPLETED, null, $source, [
            'reason' => 'payment_success',
        ]);
    }

    /**
     * Live board buckets keyed by canonical status.
     *
     * @return array<string, \Illuminate\Support\Collection<int, Order>>
     */
    public function boardForRestaurant(int $restaurantId, ?Carbon $completedOn = null): array
    {
        $completedOn = $completedOn ?? Carbon::today();
        $board = [];

        foreach (OrderWorkflow::liveStatuses() as $status) {
            $board[$status] = Order::with(['items.menuItem', 'waiter'])
                ->where('restaurant_id', $restaurantId)
                ->whereIn('status', OrderWorkflow::storageVariants($status))
                ->latest()
                ->get();
        }

        $board[OrderWorkflow::COMPLETED] = Order::with(['items.menuItem', 'waiter'])
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::terminalStatuses())
            ->where(function ($q) use ($completedOn) {
                $q->whereDate('completed_at', $completedOn)
                    ->orWhere(function ($q2) use ($completedOn) {
                        $q2->whereNull('completed_at')->whereDate('created_at', $completedOn);
                    });
            })
            ->latest()
            ->take(30)
            ->get();

        return $board;
    }

    /**
     * Dashboard workflow metrics: counts, avg time spent per stage, bottlenecks.
     *
     * @return array{
     *     segments: list<array{key: string, label: string, count: int, color: string, avg_minutes: float|null}>,
     *     total: int,
     *     stage_times: list<array{key: string, label: string, avg_minutes: float, sample_size: int, threshold_minutes: int, is_bottleneck: bool}>,
     *     bottlenecks: list<array{key: string, label: string, avg_minutes: float, threshold_minutes: int, severity: string}>,
     *     avg_total_minutes: float|null
     * }
     */
    public function dashboardMetrics(int $restaurantId, ?Carbon $day = null): array
    {
        $day = $day ?? Carbon::today();
        $thresholds = OrderWorkflow::bottleneckThresholds();

        $liveCounts = [];
        foreach (OrderWorkflow::liveStatuses() as $status) {
            $liveCounts[$status] = Order::query()
                ->where('restaurant_id', $restaurantId)
                ->whereIn('status', OrderWorkflow::storageVariants($status))
                ->count();
        }

        $completedToday = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::terminalStatuses())
            ->where(function ($q) use ($day) {
                $q->whereDate('completed_at', $day)
                    ->orWhere(function ($q2) use ($day) {
                        $q2->whereNull('completed_at')->whereDate('created_at', $day);
                    });
            })
            ->count();

        $segments = [];
        $total = 0;
        $stageTimes = $this->averageStageDurations($restaurantId, $day->copy()->subDays(6)->startOfDay(), $day->copy()->endOfDay());

        foreach (OrderWorkflow::PIPELINE as $status) {
            $count = $status === OrderWorkflow::COMPLETED
                ? $completedToday
                : (int) ($liveCounts[$status] ?? 0);

            $avg = $stageTimes[$status]['avg_minutes'] ?? null;

            $segments[] = [
                'key' => $status,
                'label' => OrderWorkflow::label($status),
                'count' => $count,
                'color' => OrderWorkflow::color($status),
                'avg_minutes' => $avg,
            ];
            $total += $count;
        }

        $stageTimeList = [];
        $bottlenecks = [];

        foreach (OrderWorkflow::liveStatuses() as $status) {
            $avg = (float) ($stageTimes[$status]['avg_minutes'] ?? 0);
            $sample = (int) ($stageTimes[$status]['sample_size'] ?? 0);
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
            $stageTimeList[] = $row;

            if ($isBottleneck) {
                $severity = $avg >= ($threshold * 2) ? 'critical' : 'warning';
                $bottlenecks[] = [
                    'key' => $status,
                    'label' => OrderWorkflow::label($status),
                    'avg_minutes' => round($avg, 1),
                    'threshold_minutes' => $threshold,
                    'severity' => $severity,
                ];
            }
        }

        usort($bottlenecks, fn ($a, $b) => $b['avg_minutes'] <=> $a['avg_minutes']);

        $avgTotal = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::terminalStatuses())
            ->whereNotNull('received_at')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$day->copy()->subDays(6)->startOfDay(), $day->copy()->endOfDay()])
            ->get(['received_at', 'completed_at'])
            ->map(fn (Order $o) => $o->received_at->diffInSeconds($o->completed_at) / 60)
            ->avg();

        return [
            'segments' => $segments,
            'total' => $total,
            'stage_times' => $stageTimeList,
            'bottlenecks' => $bottlenecks,
            'avg_total_minutes' => $avgTotal !== null ? round((float) $avgTotal, 1) : null,
        ];
    }

    /**
     * @return array<string, array{avg_minutes: float, sample_size: int}>
     */
    public function averageStageDurations(int $restaurantId, Carbon $from, Carbon $to): array
    {
        $events = OrderStatusEvent::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('duration_seconds')
            ->whereNotNull('from_status')
            ->get(['from_status', 'duration_seconds']);

        $buckets = [];
        foreach ($events as $event) {
            $stage = OrderWorkflow::normalize($event->from_status);
            if (! in_array($stage, OrderWorkflow::liveStatuses(), true)) {
                continue;
            }
            $buckets[$stage][] = (int) $event->duration_seconds;
        }

        // Fallback: use timestamp deltas on completed orders when events are sparse.
        if (count($buckets) < 2) {
            $orders = Order::query()
                ->where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', [$from, $to])
                ->get([
                    'received_at', 'accepted_at', 'preparing_at', 'ready_at', 'served_at', 'completed_at',
                ]);

            $pairs = [
                OrderWorkflow::RECEIVED => ['received_at', 'accepted_at'],
                OrderWorkflow::ACCEPTED => ['accepted_at', 'preparing_at'],
                OrderWorkflow::PREPARING => ['preparing_at', 'ready_at'],
                OrderWorkflow::READY => ['ready_at', 'served_at'],
                OrderWorkflow::SERVED => ['served_at', 'completed_at'],
            ];

            foreach ($orders as $order) {
                foreach ($pairs as $stage => [$startCol, $endCol]) {
                    $start = $order->{$startCol};
                    $end = $order->{$endCol};
                    if ($start && $end && $end->greaterThan($start)) {
                        $buckets[$stage][] = $start->diffInSeconds($end);
                    }
                }
            }
        }

        $result = [];
        foreach (OrderWorkflow::liveStatuses() as $stage) {
            $samples = $buckets[$stage] ?? [];
            $count = count($samples);
            $avgSeconds = $count > 0 ? array_sum($samples) / $count : 0;
            $result[$stage] = [
                'avg_minutes' => round($avgSeconds / 60, 1),
                'sample_size' => $count,
            ];
        }

        return $result;
    }

    public function timeInCurrentStageSeconds(Order $order): int
    {
        $column = OrderWorkflow::timestampColumn($order->status);
        $started = $column ? $order->{$column} : null;
        if (! $started) {
            $started = $order->updated_at ?? $order->created_at;
        }

        return $started ? max(0, $started->diffInSeconds(now())) : 0;
    }
}
