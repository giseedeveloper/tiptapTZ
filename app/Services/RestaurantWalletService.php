<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Restaurant;
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

    public function totalEarned(Restaurant $restaurant): float
    {
        return (float) Payment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', $this->paidPaymentStatuses())
            ->sum('amount');
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
        $available = $this->totalEarned($restaurant)
            - $this->totalWithdrawn($restaurant)
            - $this->pendingWithdrawalTotal($restaurant);

        return max(0, round($available, 2));
    }

    /**
     * @return array{
     *     total_earned: float,
     *     total_withdrawn: float,
     *     pending_withdrawals: float,
     *     available_balance: float
     * }
     */
    public function summary(Restaurant $restaurant): array
    {
        return [
            'total_earned' => round($this->totalEarned($restaurant), 2),
            'total_withdrawn' => round($this->totalWithdrawn($restaurant), 2),
            'pending_withdrawals' => round($this->pendingWithdrawalTotal($restaurant), 2),
            'available_balance' => $this->availableBalance($restaurant),
        ];
    }
}
