<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Withdrawal;

class RestaurantWalletService
{
    /**
     * @return list<string>
     */
    private function paidPaymentStatuses(): array
    {
        return ['paid', 'completed'];
    }

    /**
     * @return list<string>
     */
    private function settledWithdrawalStatuses(): array
    {
        return ['approved', 'paid'];
    }

    public function commissionRate(): float
    {
        return max(0, min(100, (float) Setting::get('commission_rate', 5)));
    }

    public function totalEarned(Restaurant $restaurant): float
    {
        return (float) Payment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', $this->paidPaymentStatuses())
            ->sum('amount');
    }

    public function platformCommission(Restaurant $restaurant): float
    {
        $gross = $this->totalEarned($restaurant);
        $rate = $this->commissionRate();

        return round($gross * ($rate / 100), 2);
    }

    public function netEarned(Restaurant $restaurant): float
    {
        return round($this->totalEarned($restaurant) - $this->platformCommission($restaurant), 2);
    }

    public function totalWithdrawn(Restaurant $restaurant): float
    {
        return (float) Withdrawal::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', $this->settledWithdrawalStatuses())
            ->sum('amount');
    }

    public function pendingWithdrawalTotal(Restaurant $restaurant): float
    {
        return (float) Withdrawal::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function availableBalance(Restaurant $restaurant): float
    {
        $available = $this->netEarned($restaurant)
            - $this->totalWithdrawn($restaurant)
            - $this->pendingWithdrawalTotal($restaurant);

        return max(0, round($available, 2));
    }

    /**
     * @return array{
     *     total_earned: float,
     *     commission_rate: float,
     *     platform_commission: float,
     *     net_earned: float,
     *     total_withdrawn: float,
     *     pending_withdrawals: float,
     *     available_balance: float
     * }
     */
    public function summary(Restaurant $restaurant): array
    {
        return [
            'total_earned' => round($this->totalEarned($restaurant), 2),
            'commission_rate' => $this->commissionRate(),
            'platform_commission' => $this->platformCommission($restaurant),
            'net_earned' => $this->netEarned($restaurant),
            'total_withdrawn' => round($this->totalWithdrawn($restaurant), 2),
            'pending_withdrawals' => round($this->pendingWithdrawalTotal($restaurant), 2),
            'available_balance' => $this->availableBalance($restaurant),
        ];
    }

    /**
     * @return array{
     *     by_type: list<array{type: string, total: float, count: int}>,
     *     by_method: list<array{method: string, total: float, count: int}>
     * }
     */
    public function paymentBreakdown(Restaurant $restaurant): array
    {
        $payments = Payment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', $this->paidPaymentStatuses())
            ->get(['amount', 'payment_type', 'method']);

        $byType = $payments
            ->groupBy(fn (Payment $payment): string => $payment->payment_type ?: 'order')
            ->map(fn ($group, string $type): array => [
                'type' => $type,
                'total' => round((float) $group->sum('amount'), 2),
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('total')
            ->values()
            ->all();

        $byMethod = $payments
            ->groupBy(fn (Payment $payment): string => $payment->method ?: 'unknown')
            ->map(fn ($group, string $method): array => [
                'method' => $method,
                'total' => round((float) $group->sum('amount'), 2),
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'by_type' => $byType,
            'by_method' => $byMethod,
        ];
    }
}
