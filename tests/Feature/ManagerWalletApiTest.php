<?php

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurantA = Restaurant::create(['name' => 'Restaurant A', 'location' => 'Dar', 'phone' => '0711111111', 'is_active' => true]);
    $this->restaurantB = Restaurant::create(['name' => 'Restaurant B', 'location' => 'Arusha', 'phone' => '0722222222', 'is_active' => true]);

    $this->managerA = User::factory()->create(['restaurant_id' => $this->restaurantA->id]);
    $this->managerA->assignRole('manager');

    $this->managerB = User::factory()->create(['restaurant_id' => $this->restaurantB->id]);
    $this->managerB->assignRole('manager');

    $this->tokenA = $this->managerA->createToken('manager-wallet-api')->plainTextToken;

    Setting::set('min_withdrawal', '1000', 'financial');
    Setting::set('commission_rate', '0', 'financial');
});

test('manager wallet api returns summary breakdown and payout profile', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 15000,
        'method' => 'ussd',
        'payment_type' => 'order',
        'status' => 'paid',
        'transaction_reference' => 'API-ORD',
    ]);

    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 5000,
        'method' => 'mobile',
        'payment_type' => 'quick',
        'status' => 'paid',
        'transaction_reference' => 'API-QK',
    ]);

    $this->restaurantA->update([
        'payout_method' => 'Mobile Money',
        'payout_details' => '255712345678',
    ]);

    $response = $this->withToken($this->tokenA)
        ->getJson('/api/v1/manager/wallet')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.summary.available_balance', 20000)
        ->assertJsonPath('data.payout_profile.is_complete', true);

    $byType = collect($response->json('data.breakdown.by_type'));
    expect((float) $byType->firstWhere('type', 'order')['total'])->toBe(15000.0)
        ->and((float) $byType->firstWhere('type', 'quick')['total'])->toBe(5000.0);
});

test('manager wallet api lists own payments and withdrawals only', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 8000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'API-PA',
    ]);

    Payment::query()->create([
        'restaurant_id' => $this->restaurantB->id,
        'amount' => 50000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'API-PB',
    ]);

    Withdrawal::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 2000,
        'status' => 'pending',
        'payment_method' => 'Mobile Money',
        'payment_details' => '255712345678',
    ]);

    $this->withToken($this->tokenA)
        ->getJson('/api/v1/manager/wallet/payments')
        ->assertOk()
        ->assertJsonPath('data.pagination.total', 1)
        ->assertJsonPath('data.items.0.transaction_reference', 'API-PA');

    $this->withToken($this->tokenA)
        ->getJson('/api/v1/manager/wallet/withdrawals')
        ->assertOk()
        ->assertJsonPath('data.pagination.total', 1)
        ->assertJsonPath('data.items.0.amount', 2000);
});

test('manager wallet api can save payout profile and submit withdrawal', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 30000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'API-WD',
    ]);

    $this->withToken($this->tokenA)
        ->putJson('/api/v1/manager/wallet/payout-profile', [
            'payout_method' => 'Bank Transfer',
            'payout_details' => 'CRDB 0123456789 · Restaurant A',
        ])
        ->assertOk()
        ->assertJsonPath('data.is_complete', true);

    $this->withToken($this->tokenA)
        ->postJson('/api/v1/manager/wallet/withdrawals', [
            'amount' => 12000,
            'use_saved_payout' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.withdrawal.payment_method', 'Bank Transfer')
        ->assertJsonPath('data.summary.available_balance', 18000);
});

test('manager wallet api export returns csv statement', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 11000,
        'method' => 'ussd',
        'payment_type' => 'order',
        'status' => 'paid',
        'transaction_reference' => 'API-CSV',
    ]);

    $response = $this->withToken($this->tokenA)
        ->get('/api/v1/manager/wallet/export');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('TIPTAP Wallet Statement')
        ->toContain('BREAKDOWN BY TYPE')
        ->toContain('API-CSV');
});

test('manager wallet web export returns csv statement', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 9000,
        'method' => 'cash',
        'payment_type' => 'order',
        'status' => 'paid',
        'transaction_reference' => 'WEB-CSV',
    ]);

    $response = $this->actingAs($this->managerA)
        ->get(route('manager.wallet.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('WEB-CSV')
        ->toContain('Available balance');
});

test('wallet page shows earnings breakdown tables', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 12000,
        'method' => 'ussd',
        'payment_type' => 'order',
        'status' => 'paid',
        'transaction_reference' => 'UI-BRK',
    ]);

    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 3000,
        'method' => 'mobile',
        'payment_type' => 'quick',
        'status' => 'paid',
        'transaction_reference' => 'UI-QK',
    ]);

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Earnings by type')
        ->assertSee('Earnings by method')
        ->assertSee('quick')
        ->assertSee('Export wallet CSV');
});
