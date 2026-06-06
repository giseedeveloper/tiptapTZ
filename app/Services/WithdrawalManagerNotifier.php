<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalStatusNotification;

class WithdrawalManagerNotifier
{
    public function notify(Withdrawal $withdrawal, string $status): void
    {
        $withdrawal->loadMissing('restaurant');

        if (! $withdrawal->restaurant_id) {
            return;
        }

        User::query()
            ->role('manager')
            ->where('restaurant_id', $withdrawal->restaurant_id)
            ->get()
            ->each(fn (User $manager) => $manager->notify(
                new WithdrawalStatusNotification($withdrawal, $status)
            ));
    }
}
