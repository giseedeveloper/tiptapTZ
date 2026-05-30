<?php

namespace App\Providers;

use App\Notifications\SalaryPaymentConfirmed;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.waiter', function ($view): void {
            if (Auth::check() && Auth::user()->hasRole('waiter')) {
                $view->with('unreadSalaryCount', Auth::user()->unreadNotifications()
                    ->where('type', SalaryPaymentConfirmed::class)
                    ->count());
            }
        });

        View::composer('*', function ($view): void {
            $view->with('currencySymbol', Money::symbol());
        });
    }
}
