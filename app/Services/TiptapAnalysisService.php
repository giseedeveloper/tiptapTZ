<?php

namespace App\Services;

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Enums\BotQrEntryType;
use App\Enums\FeedbackType;
use App\Models\BotEvent;
use App\Models\BotSession;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TiptapAnalysisService
{
    /**
     * @return array<string, mixed>
     */
    public function platformSnapshot(?int $restaurantId = null, int $trendDays = 30): array
    {
        $trendDays = max(7, min($trendDays, 90));

        return [
            'restaurants' => $this->restaurantSplit(),
            'orders' => $this->orderPeriodCounts($restaurantId),
            'revenue_trend' => $this->dailyRevenueTrend($trendDays, $restaurantId),
            'top_restaurants' => $this->topRestaurants(5),
            'filters' => [
                'restaurant_id' => $restaurantId,
                'trend_days' => $trendDays,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function whatsappEngagement(?int $restaurantId = null, int $days = 30): array
    {
        $days = max(7, min($days, 90));
        $start = now()->subDays($days - 1)->startOfDay();

        $usageQuery = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', BotEngagementEvent::values());

        if ($restaurantId !== null) {
            $usageQuery->where('restaurant_id', $restaurantId);
        }

        $counts = $usageQuery
            ->select('event_type', DB::raw('COUNT(*) as total'))
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $optionUsage = collect(BotEngagementEvent::cases())
            ->map(function (BotEngagementEvent $event) use ($counts): array {
                return [
                    'key' => $event->value,
                    'label' => $event->label(),
                    'value' => (int) ($counts[$event->value] ?? 0),
                    'color' => $event->color(),
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->all();

        return [
            'option_usage' => $optionUsage,
            'total_events' => (int) array_sum(array_column($optionUsage, 'value')),
            'daily_trend' => $this->dailyEngagementTrend($days, $restaurantId),
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function qrEntryPoints(?int $restaurantId = null, int $days = 30): array
    {
        $days = max(7, min($days, 90));
        $start = now()->subDays($days - 1)->startOfDay();

        $query = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', BotQrEntryType::values());

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $counts = $query
            ->select('event_type', DB::raw('COUNT(*) as total'))
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $split = collect(BotQrEntryType::cases())
            ->map(fn (BotQrEntryType $type) => [
                'key' => $type->value,
                'label' => $type->label(),
                'value' => (int) ($counts[$type->value] ?? 0),
                'color' => $type->color(),
            ])
            ->sortByDesc('value')
            ->values()
            ->all();

        $total = (int) array_sum(array_column($split, 'value'));

        return [
            'split' => $split,
            'total_scans' => $total,
            'per_restaurant' => $this->qrEntryPerRestaurant($start, $restaurantId),
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerJourneyFunnel(?int $restaurantId = null, int $days = 30): array
    {
        $days = max(7, min($days, 90));
        $start = now()->subDays($days - 1)->startOfDay();

        $qrCount = $this->eventCount($start, BotQrEntryType::values(), $restaurantId);
        $stepCounts = [
            'qr_scan' => $qrCount,
        ];

        foreach (BotFunnelStep::orderedSteps() as $step) {
            $stepCounts[$step->value] = $this->eventCount($start, [$step->value], $restaurantId);
        }

        $steps = [];

        $steps[] = [
            'key' => 'qr_scan',
            'label' => 'QR Scan',
            'count' => $qrCount,
            'conversion_pct' => $qrCount > 0 ? 100.0 : 0.0,
            'drop_off' => 0,
        ];

        foreach (BotFunnelStep::orderedSteps() as $step) {
            $count = $stepCounts[$step->value];
            $conversionBase = $steps[array_key_last($steps)]['count'];
            $conversionPct = $conversionBase > 0
                ? round(($count / $conversionBase) * 100, 1)
                : 0.0;

            $steps[] = [
                'key' => $step->value,
                'label' => $step->label(),
                'count' => $count,
                'conversion_pct' => $conversionPct,
                'drop_off' => max(0, $conversionBase - $count),
            ];
        }

        $biggestDropOff = collect($steps)
            ->sortByDesc('drop_off')
            ->first();

        return [
            'steps' => $steps,
            'biggest_drop_off' => $biggestDropOff && $biggestDropOff['drop_off'] > 0
                ? [
                    'step' => $biggestDropOff['label'],
                    'drop_off' => $biggestDropOff['drop_off'],
                ]
                : null,
            'payment_methods' => $this->funnelPaymentMethods($start, $restaurantId),
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function feedbackOverview(?int $restaurantId = null, int $days = 30, int $recentLimit = 10): array
    {
        $days = max(7, min($days, 90));
        $start = now()->subDays($days - 1)->startOfDay();
        $recentLimit = max(5, min($recentLimit, 25));

        $base = Feedback::withoutGlobalScope(RestaurantScope::class)
            ->where('created_at', '>=', $start);

        if ($restaurantId !== null) {
            $base->where('restaurant_id', $restaurantId);
        }

        $ratingCounts = (clone $base)
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingDistribution = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $ratingDistribution[] = [
                'stars' => $stars,
                'count' => (int) ($ratingCounts[$stars] ?? 0),
            ];
        }

        $byType = collect(FeedbackType::cases())
            ->map(function (FeedbackType $type) use ($base): array {
                $typeQuery = (clone $base)->where('type', $type->value);

                return [
                    'key' => $type->value,
                    'label' => ucfirst($type->value),
                    'count' => (int) (clone $typeQuery)->count(),
                    'avg_rating' => round((float) (clone $typeQuery)->avg('rating'), 1),
                ];
            })
            ->values()
            ->all();

        $recentComments = (clone $base)
            ->with('restaurant:id,name')
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->limit($recentLimit)
            ->get()
            ->map(fn (Feedback $feedback) => [
                'id' => $feedback->id,
                'rating' => $feedback->rating,
                'comment' => $feedback->comment,
                'type' => $feedback->type->value,
                'restaurant_name' => $feedback->restaurant?->name,
                'created_at' => $feedback->created_at?->toIso8601String(),
            ])
            ->all();

        $avgByRestaurant = Restaurant::query()
            ->select('restaurants.id', 'restaurants.name')
            ->selectSub(
                Feedback::query()
                    ->withoutGlobalScopes()
                    ->where('created_at', '>=', $start)
                    ->whereColumn('feedback.restaurant_id', 'restaurants.id')
                    ->selectRaw('ROUND(COALESCE(AVG(rating), 0), 1)'),
                'avg_rating'
            )
            ->selectSub(
                Feedback::query()
                    ->withoutGlobalScopes()
                    ->where('created_at', '>=', $start)
                    ->whereColumn('feedback.restaurant_id', 'restaurants.id')
                    ->selectRaw('COUNT(*)'),
                'review_count'
            )
            ->when($restaurantId !== null, fn ($query) => $query->where('restaurants.id', $restaurantId))
            ->orderByDesc('avg_rating')
            ->get()
            ->filter(fn ($row) => (int) $row->review_count > 0)
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'avg_rating' => (float) $row->avg_rating,
                'review_count' => (int) $row->review_count,
                'url' => route('admin.restaurants.show', $row->id),
            ])
            ->all();

        $lowRatingAlerts = collect($avgByRestaurant)
            ->filter(fn (array $row) => $row['avg_rating'] < 3.5 && $row['review_count'] >= 3)
            ->sortBy('avg_rating')
            ->values()
            ->all();

        return [
            'rating_distribution' => $ratingDistribution,
            'by_type' => $byType,
            'recent_comments' => $recentComments,
            'avg_rating_by_restaurant' => $avgByRestaurant,
            'low_rating_alerts' => $lowRatingAlerts,
            'summary' => [
                'total_reviews' => (int) (clone $base)->count(),
                'avg_rating' => round((float) (clone $base)->avg('rating'), 1),
            ],
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
                'recent_limit' => $recentLimit,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tipsAndPayments(?int $restaurantId = null, int $days = 30): array
    {
        $days = max(7, min($days, 90));
        $start = $this->periodStart($days);

        $tipsQuery = Tip::withoutGlobalScope(RestaurantScope::class)
            ->where('created_at', '>=', $start);

        if ($restaurantId !== null) {
            $tipsQuery->where('restaurant_id', $restaurantId);
        }

        $tipCount = (int) (clone $tipsQuery)->count();
        $totalTips = (float) (clone $tipsQuery)->sum('amount');

        $paymentsQuery = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $start);

        if ($restaurantId !== null) {
            $paymentsQuery->where('restaurant_id', $restaurantId);
        }

        $paymentMethods = $this->paymentMethodBreakdown(clone $paymentsQuery);
        $paymentPurpose = $this->paymentPurposeSplit(clone $paymentsQuery);

        return [
            'tips' => [
                'total_amount' => $totalTips,
                'avg_amount' => $tipCount > 0 ? round($totalTips / $tipCount, 2) : 0.0,
                'count' => $tipCount,
            ],
            'top_tipped_waiters' => $this->topTippedWaiters($start, $restaurantId),
            'payment_methods' => $paymentMethods,
            'payment_purpose' => $paymentPurpose,
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function languageAndBehavior(?int $restaurantId = null, int $days = 30): array
    {
        $days = max(7, min($days, 90));
        $start = $this->periodStart($days);

        $languageSplit = $this->languageSplitFromSessions($start, $restaurantId);
        $perRestaurant = $this->languagePreferencePerRestaurant($start, $restaurantId);
        $peakHours = $this->peakHours($start, $restaurantId);

        return [
            'language_split' => $languageSplit,
            'per_restaurant' => $perRestaurant,
            'peak_hours' => $peakHours,
            'filters' => [
                'restaurant_id' => $restaurantId,
                'days' => $days,
            ],
        ];
    }

    /**
     * @return list<array{id: int, name: string, restaurant_name: ?string, total_tips: float, tip_count: int}>
     */
    private function topTippedWaiters(\Illuminate\Support\Carbon $start, ?int $restaurantId, int $limit = 10): array
    {
        $query = Tip::withoutGlobalScope(RestaurantScope::class)
            ->where('created_at', '>=', $start)
            ->whereNotNull('waiter_id');

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $rows = $query
            ->select('waiter_id', DB::raw('SUM(amount) as total_tips'), DB::raw('COUNT(*) as tip_count'))
            ->groupBy('waiter_id')
            ->orderByDesc('total_tips')
            ->limit($limit)
            ->get();

        $waiters = User::query()
            ->whereIn('id', $rows->pluck('waiter_id'))
            ->with('restaurant:id,name')
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($waiters): array {
            $waiter = $waiters->get($row->waiter_id);

            return [
                'id' => (int) $row->waiter_id,
                'name' => $waiter?->name ?? 'Waiter #'.$row->waiter_id,
                'restaurant_name' => $waiter?->restaurant?->name,
                'total_tips' => (float) $row->total_tips,
                'tip_count' => (int) $row->tip_count,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{label: string, key: string, value: int, amount: float, color: string}>
     */
    private function paymentMethodBreakdown($paymentsQuery): array
    {
        $colors = [
            'cash' => '#f59e0b',
            'ussd' => '#10b981',
            'card' => '#8C71F6',
        ];

        $rows = $paymentsQuery
            ->select('method', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as amount'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        return $rows->map(function ($row, int $index) use ($colors): array {
            $key = strtolower((string) $row->method);

            return [
                'key' => $key,
                'label' => ucfirst($key ?: 'unknown'),
                'value' => (int) $row->total,
                'amount' => (float) $row->amount,
                'color' => $colors[$key] ?? ['#06b6d4', '#ec4899', '#6D52E8'][$index % 3],
            ];
        })->values()->all();
    }

    /**
     * @return list<array{label: string, key: string, value: int, amount: float, color: string}>
     */
    private function paymentPurposeSplit($paymentsQuery): array
    {
        $billQuery = clone $paymentsQuery;
        $quickQuery = clone $paymentsQuery;

        $billCount = (int) $billQuery->where(function ($query): void {
            $query->where('payment_type', 'order')
                ->orWhereNull('payment_type')
                ->orWhere('payment_type', '');
        })->count();

        $billAmount = (float) (clone $paymentsQuery)->where(function ($query): void {
            $query->where('payment_type', 'order')
                ->orWhereNull('payment_type')
                ->orWhere('payment_type', '');
        })->sum('amount');

        $quickCount = (int) $quickQuery->where('payment_type', 'quick')->count();
        $quickAmount = (float) (clone $paymentsQuery)->where('payment_type', 'quick')->sum('amount');

        return [
            [
                'key' => 'order',
                'label' => 'Bill Payments',
                'value' => $billCount,
                'amount' => $billAmount,
                'color' => '#8C71F6',
            ],
            [
                'key' => 'quick',
                'label' => 'Quick Payments',
                'value' => $quickCount,
                'amount' => $quickAmount,
                'color' => '#06b6d4',
            ],
        ];
    }

    /**
     * @return list<array{key: string, label: string, value: int, color: string}>
     */
    private function languageSplitFromSessions(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $sessions = $this->sessionsInPeriod($start, $restaurantId);
        $eventLangs = $this->languageCountsFromChangeEvents($start, $restaurantId);

        $counts = [];

        foreach ($sessions as $session) {
            $lang = strtolower((string) ($session->lang ?: 'en'));
            $counts[$lang] = ($counts[$lang] ?? 0) + 1;
        }

        foreach ($eventLangs as $lang => $count) {
            $counts[$lang] = ($counts[$lang] ?? 0) + $count;
        }

        if ($counts === []) {
            return [];
        }

        $colors = ['#8C71F6', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'];
        $index = 0;

        return collect($counts)
            ->sortByDesc(fn (int $count) => $count)
            ->map(function (int $count, string $lang) use (&$index, $colors): array {
                $color = $colors[$index % count($colors)];
                $index++;

                return [
                    'key' => $lang,
                    'label' => $this->languageLabel($lang),
                    'value' => $count,
                    'color' => $color,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, preferred: string, preferred_label: string, languages: list<array{key: string, label: string, value: int}>}>
     */
    private function languagePreferencePerRestaurant(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $sessions = $this->sessionsInPeriod($start, $restaurantId);
        $grouped = [];

        foreach ($sessions as $session) {
            $sessionRestaurantId = (int) ($session->data['restaurant_id'] ?? 0);
            if ($sessionRestaurantId <= 0) {
                continue;
            }

            $lang = strtolower((string) ($session->lang ?: 'en'));
            $grouped[$sessionRestaurantId][$lang] = ($grouped[$sessionRestaurantId][$lang] ?? 0) + 1;
        }

        if ($grouped === []) {
            return [];
        }

        $names = Restaurant::query()
            ->whereIn('id', array_keys($grouped))
            ->pluck('name', 'id');

        $result = [];

        foreach ($grouped as $id => $langCounts) {
            arsort($langCounts);
            $preferred = (string) array_key_first($langCounts);

            $result[] = [
                'id' => (int) $id,
                'name' => $names[$id] ?? 'Restaurant #'.$id,
                'preferred' => $preferred,
                'preferred_label' => $this->languageLabel($preferred),
                'languages' => collect($langCounts)
                    ->map(fn (int $count, string $lang) => [
                        'key' => $lang,
                        'label' => $this->languageLabel($lang),
                        'value' => $count,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return collect($result)->sortByDesc(fn (array $row) => array_sum(array_column($row['languages'], 'value')))->values()->all();
    }

    /**
     * @return array{
     *     events: list<array{hour: int, label: string, count: int}>,
     *     sessions: list<array{hour: int, label: string, count: int}>,
     *     peak_event_hour: ?int,
     *     peak_session_hour: ?int
     * }
     */
    private function peakHours(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $eventQuery = BotEvent::query()->where('occurred_at', '>=', $start);
        $sessionQuery = BotSession::query()->where('last_message_at', '>=', $start);

        if ($restaurantId !== null) {
            $eventQuery->where('restaurant_id', $restaurantId);
            $sessionQuery->where(function ($query) use ($restaurantId): void {
                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("json_extract(data, '$.restaurant_id') = ?", [$restaurantId]);
                } else {
                    $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.restaurant_id')) = ?", [(string) $restaurantId]);
                }
            });
        }

        $eventRows = $eventQuery
            ->selectRaw($this->hourSelectExpression('occurred_at').' as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $sessionRows = $sessionQuery
            ->selectRaw($this->hourSelectExpression('last_message_at').' as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $events = $this->fillHourlySeries($eventRows);
        $sessions = $this->fillHourlySeries($sessionRows);

        return [
            'events' => $events,
            'sessions' => $sessions,
            'peak_event_hour' => $this->peakHourFromSeries($events),
            'peak_session_hour' => $this->peakHourFromSeries($sessions),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, BotSession>
     */
    private function sessionsInPeriod(\Illuminate\Support\Carbon $start, ?int $restaurantId): Collection
    {
        $query = BotSession::query()->where('last_message_at', '>=', $start);

        if ($restaurantId !== null) {
            $query->where(function ($builder) use ($restaurantId): void {
                if (DB::connection()->getDriverName() === 'sqlite') {
                    $builder->whereRaw("json_extract(data, '$.restaurant_id') = ?", [$restaurantId]);
                } else {
                    $builder->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.restaurant_id')) = ?", [(string) $restaurantId]);
                }
            });
        }

        return $query->get();
    }

    /**
     * @return array<string, int>
     */
    private function languageCountsFromChangeEvents(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $query = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->where('event_type', BotEngagementEvent::ChangeLanguage->value);

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $counts = [];

        foreach ($query->get(['metadata']) as $event) {
            $lang = strtolower((string) ($event->metadata['lang'] ?? $event->metadata['language'] ?? ''));
            if ($lang === '') {
                continue;
            }

            $counts[$lang] = ($counts[$lang] ?? 0) + 1;
        }

        return $counts;
    }

    private function periodStart(int $days): \Illuminate\Support\Carbon
    {
        return now()->subDays($days - 1)->startOfDay();
    }

    private function languageLabel(string $code): string
    {
        return match (strtolower($code)) {
            'en' => 'English',
            'sw' => 'Swahili',
            default => strtoupper($code),
        };
    }

    private function hourSelectExpression(string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "cast(strftime('%H', {$column}) as integer)";
        }

        return "HOUR({$column})";
    }

    /**
     * @param  Collection<int|string, int>  $rows
     * @return list<array{hour: int, label: string, count: int}>
     */
    private function fillHourlySeries(Collection $rows): array
    {
        $series = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $series[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'count' => (int) ($rows[$hour] ?? $rows[(string) $hour] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @param  list<array{hour: int, label: string, count: int}>  $series
     */
    private function peakHourFromSeries(array $series): ?int
    {
        if ($series === []) {
            return null;
        }

        $peak = collect($series)->sortByDesc('count')->first();

        return ($peak['count'] ?? 0) > 0 ? (int) $peak['hour'] : null;
    }

    /**
     * @param  list<string>  $eventTypes
     */
    private function eventCount(\Illuminate\Support\Carbon $start, array $eventTypes, ?int $restaurantId): int
    {
        $query = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', $eventTypes);

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        return (int) $query->count();
    }

    /**
     * @return list<array{id: int, name: string, waiter: int, table: int, restaurant: int, total: int, preferred: string, insight: string}>
     */
    private function qrEntryPerRestaurant(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $rows = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', BotQrEntryType::values())
            ->when($restaurantId !== null, fn ($query) => $query->where('restaurant_id', $restaurantId))
            ->whereNotNull('restaurant_id')
            ->select('restaurant_id', 'event_type', DB::raw('COUNT(*) as total'))
            ->groupBy('restaurant_id', 'event_type')
            ->get();

        $grouped = $rows->groupBy('restaurant_id');
        $restaurantNames = Restaurant::query()
            ->whereIn('id', $grouped->keys())
            ->pluck('name', 'id');

        $result = [];

        foreach ($grouped as $id => $events) {
            $waiter = (int) ($events->firstWhere('event_type', BotQrEntryType::Waiter->value)?->total ?? 0);
            $table = (int) ($events->firstWhere('event_type', BotQrEntryType::Table->value)?->total ?? 0);
            $restaurant = (int) ($events->firstWhere('event_type', BotQrEntryType::Restaurant->value)?->total ?? 0);
            $total = $waiter + $table + $restaurant;

            if ($total === 0) {
                continue;
            }

            $preferred = collect([
                'waiter' => $waiter,
                'table' => $table,
                'restaurant' => $restaurant,
            ])->sortDesc()->keys()->first();

            $preferredLabel = match ($preferred) {
                'waiter' => 'waiter QR',
                'table' => 'table QR',
                default => 'restaurant tag',
            };

            $name = $restaurantNames[$id] ?? 'Restaurant #'.$id;

            $result[] = [
                'id' => (int) $id,
                'name' => $name,
                'waiter' => $waiter,
                'table' => $table,
                'restaurant' => $restaurant,
                'total' => $total,
                'preferred' => $preferred,
                'insight' => "{$name} — customers prefer {$preferredLabel}",
            ];
        }

        return collect($result)->sortByDesc('total')->values()->all();
    }

    /**
     * @return list<array{label: string, value: int, color: string}>
     */
    private function funnelPaymentMethods(\Illuminate\Support\Carbon $start, ?int $restaurantId): array
    {
        $query = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $start);

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $cash = (int) (clone $query)->where('method', 'cash')->count();
        $digital = (int) (clone $query)->where('method', '!=', 'cash')->count();

        return [
            ['label' => 'Cash', 'value' => $cash, 'color' => '#f59e0b'],
            ['label' => 'Digital', 'value' => $digital, 'color' => '#10b981'],
        ];
    }

    /**
     * @return array{active: int, inactive: int, total: int, segments: list<array{label: string, value: int, color: string}>}
     */
    private function restaurantSplit(): array
    {
        $active = Restaurant::query()->where('is_active', true)->count();
        $inactive = Restaurant::query()->where('is_active', false)->count();

        return [
            'active' => $active,
            'inactive' => $inactive,
            'total' => $active + $inactive,
            'segments' => [
                ['label' => 'Active', 'value' => $active, 'color' => '#10b981'],
                ['label' => 'Inactive', 'value' => $inactive, 'color' => '#f43f5e'],
            ],
        ];
    }

    /**
     * @return array{today: int, week: int, month: int}
     */
    private function orderPeriodCounts(?int $restaurantId): array
    {
        $base = Order::query()->withoutGlobalScopes();

        if ($restaurantId !== null) {
            $base->where('restaurant_id', $restaurantId);
        }

        return [
            'today' => (clone $base)->where('created_at', '>=', now()->startOfDay())->count(),
            'week' => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => (clone $base)->where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * @return list<array{label: string, date: string, revenue: float}>
     */
    private function dailyRevenueTrend(int $days, ?int $restaurantId): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $query = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $start);

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $rows = $query
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($days, $rows, 'revenue');
    }

    /**
     * @return list<array{id: int, name: string, revenue: float, orders: int, url: string}>
     */
    private function topRestaurants(int $limit): array
    {
        return Restaurant::query()
            ->select('restaurants.id', 'restaurants.name')
            ->selectSub(
                Payment::query()
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereColumn('payments.restaurant_id', 'restaurants.id')
                    ->selectRaw('COALESCE(SUM(amount), 0)'),
                'revenue'
            )
            ->selectSub(
                Order::query()
                    ->withoutGlobalScopes()
                    ->whereColumn('orders.restaurant_id', 'restaurants.id')
                    ->selectRaw('COUNT(*)'),
                'orders'
            )
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'revenue' => (float) $restaurant->revenue,
                'orders' => (int) $restaurant->orders,
                'url' => route('admin.restaurants.show', $restaurant->id),
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, date: string, count: int}>
     */
    private function dailyEngagementTrend(int $days, ?int $restaurantId): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $query = BotEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', BotEngagementEvent::values());

        if ($restaurantId !== null) {
            $query->where('restaurant_id', $restaurantId);
        }

        $rows = $query
            ->selectRaw('DATE(occurred_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($days, $rows, 'count');
    }

    /**
     * @param  Collection<string, float|int>  $rows
     * @return list<array<string, mixed>>
     */
    private function fillDailySeries(int $days, Collection $rows, string $valueKey): array
    {
        $series = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $series[] = [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                $valueKey => $valueKey === 'revenue'
                    ? (float) ($rows[$key] ?? 0)
                    : (int) ($rows[$key] ?? 0),
            ];
        }

        return $series;
    }
}
