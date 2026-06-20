<?php

namespace App\Providers;

use App\Contracts\DockerControlContract;
use App\Notifications\SalaryPaymentConfirmed;
use App\Services\Docker\DockerControlService;
use App\Support\AdminPortalAccess;
use App\Support\LandingPageContent;
use App\Support\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

        View::composer('welcome', function ($view): void {
            $view->with('plans', \App\Models\SubscriptionPackage::query()->active()->ordered()->get());
        });

        View::composer(['components.admin-layout', 'admin.*'], function ($view): void {
            $view->with('adminAccess', AdminPortalAccess::class);
        });

        Blade::if('adminCan', function (string $permission): bool {
            return AdminPortalAccess::can(auth()->user(), $permission);
        });
    }
}
