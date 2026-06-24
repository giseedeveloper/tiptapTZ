<?php

use App\Models\SubscriptionPackage;

function makeSubscriptionPackage(array $attributes = []): SubscriptionPackage
{
    return new SubscriptionPackage(array_merge([
        'slug' => 'business',
        'price' => 50000,
        'billing_period' => 'monthly',
        'currency' => 'TZS',
    ], $attributes));
}

it('calculates annual pricing as ten monthly payments', function () {
    $plan = makeSubscriptionPackage();

    expect($plan->supportsAnnualBilling())->toBeTrue();
    expect($plan->annualTotalPrice())->toBe(500000.0);
    expect($plan->annualPriceLabel())->toBe('TZS 500,000');
    expect($plan->annualMonthlyEquivalentLabel())->toBe('TZS 41,667');
});

it('does not offer annual billing for free or non monthly plans', function () {
    $freePlan = makeSubscriptionPackage(['price' => 0, 'billing_period' => 'trial']);
    $yearlyPlan = makeSubscriptionPackage(['billing_period' => 'yearly']);

    expect($freePlan->supportsAnnualBilling())->toBeFalse();
    expect($yearlyPlan->supportsAnnualBilling())->toBeFalse();
});

it('identifies enterprise plan by slug', function () {
    $enterprise = makeSubscriptionPackage(['slug' => 'enterprise']);

    expect($enterprise->isEnterprisePlan())->toBeTrue();
});
