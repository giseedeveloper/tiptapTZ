<?php

namespace App\Providers;

use App\Contracts\DockerControlContract;
use App\Notifications\SalaryPaymentConfirmed;
use App\Services\Docker\DockerControlService;
use App\Support\LandingPageContent;
use App\Support\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DockerControlContract::class, DockerControlService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin-search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('bot-token', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('docker-control', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

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

        View::composer(['welcome', 'partials.landing-contact', 'partials.social-links'], function ($view): void {
            $view->with('landing', LandingPageContent::viewData());
        });
    }
}
