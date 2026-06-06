<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalSubmissionService
{
    public function __construct(
        private readonly RestaurantWalletService $wallet,
    ) {}

    public function submit(Restaurant $restaurant, float $amount, string $paymentMethod, string $paymentDetails): Withdrawal
    {
        return DB::transaction(function () use ($restaurant, $amount, $paymentMethod, $paymentDetails): Withdrawal {
            Restaurant::query()
                ->whereKey($restaurant->id)
                ->lockForUpdate()
                ->first();

            $restaurant->refresh();

            $available = $this->wallet->availableBalance($restaurant);

            if ($amount > $available) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount exceeds available wallet balance ('.number_format($available, 0).').',
                ]);
            }

            return Withdrawal::query()->create([
                'restaurant_id' => $restaurant->id,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_details' => $paymentDetails,
            ]);
        });
    }
}
