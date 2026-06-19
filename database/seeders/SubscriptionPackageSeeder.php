<?php

namespace Database\Seeders;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $currency = config('tiptap.currency_code', 'TZS');
        $isZa = config('tiptap.market', 'za') === 'za';

        $packages = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'tagline' => 'Perfect for getting started',
                'description' => 'Try TipTap free for 14 days. No card required.',
                'price' => 0,
                'currency' => $currency,
                'billing_period' => 'trial',
                'trial_days' => 14,
                'table_limit' => 10,
                'waiter_limit' => 3,
                'features' => ['QR ordering', 'WhatsApp bot (TipTap Rafiki)', 'Basic analytics'],
                'capabilities' => [],
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'tagline' => 'Most popular for growing venues',
                'description' => 'Everything you need to run a busy restaurant.',
                'price' => $isZa ? 499 : 50000,
                'currency' => $currency,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'table_limit' => null,
                'waiter_limit' => null,
                'features' => ['QR ordering', 'WhatsApp bot (TipTap Rafiki)', 'Unlimited tables', 'Unlimited waiters', 'Mobile money payments', 'Kitchen display', 'Advanced analytics'],
                'capabilities' => ['kitchen_display', 'mobile_payments', 'advanced_analytics'],
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'tagline' => 'For groups & franchises',
                'description' => 'Custom limits, dedicated onboarding and support.',
                'price' => $isZa ? 1499 : 150000,
                'currency' => $currency,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'table_limit' => null,
                'waiter_limit' => null,
                'features' => ['QR ordering', 'WhatsApp bot (TipTap Rafiki)', 'Unlimited tables', 'Unlimited waiters', 'Mobile money payments', 'Kitchen display', 'Advanced analytics'],
                'capabilities' => ['kitchen_display', 'mobile_payments', 'advanced_analytics'],
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::query()->updateOrCreate(
                ['slug' => $package['slug']],
                $package,
            );
        }
    }
}
