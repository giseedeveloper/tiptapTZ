<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\RestaurantWalletService;
use App\Services\SystemPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->restaurantA = Restaurant::create(['name' => 'Restaurant A', 'location' => 'Dar', 'phone' => '0711111111', 'is_active' => true]);
    $this->restaurantB = Restaurant::create(['name' => 'Restaurant B', 'location' => 'Arusha', 'phone' => '0722222222', 'is_active' => true]);

    $this->managerA = User::factory()->create(['restaurant_id' => $this->restaurantA->id]);
    $this->managerA->assignRole('manager');

    $this->managerB = User::factory()->create(['restaurant_id' => $this->restaurantB->id]);
    $this->managerB->assignRole('manager');

    Setting::set('min_withdrawal', '1000', 'financial');
    Setting::set('commission_rate', '0', 'financial');
});

test('wallet deducts admin commission rate from available balance', function () {
    Setting::set('commission_rate', '5', 'financial');

    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 100000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-COMM',
    ]);

    $wallet = app(RestaurantWalletService::class);

    expect($wallet->totalEarned($this->restaurantA))->toBe(100000.0)
        ->and($wallet->platformCommission($this->restaurantA))->toBe(5000.0)
        ->and($wallet->netEarned($this->restaurantA))->toBe(95000.0)
        ->and($wallet->availableBalance($this->restaurantA))->toBe(95000.0);

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Platform fee (5%)')
        ->assertSee('95,000')
        ->assertSee('100,000');
});

test('manager wallet shows admin rejection note', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 20000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-REJ',
    ]);

    Withdrawal::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 5000,
        'status' => 'rejected',
        'payment_method' => 'Mobile Money',
        'payment_details' => '255712345678',
        'admin_note' => 'Invalid account number provided.',
    ]);

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Admin note:')
        ->assertSee('Invalid account number provided.');
});

test('super admin can save platform payment integration', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.payment-integration.update'), [
            'payment_vendor_id' => 'TILL123',
            'payment_api_key' => 'key-test',
            'payment_api_secret' => 'secret-test',
            'payment_is_live' => '0',
        ])
        ->assertRedirect();

    expect(app(SystemPaymentGateway::class)->isConfigured())->toBeTrue()
        ->and(Setting::get('payment_vendor_id'))->toBe('TILL123');
});

test('super admin can view payment integration page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payment-integration.index'))
        ->assertOk()
        ->assertSee('Payment Integration');
});

test('restaurant uses system gateway credentials not own selcom fields', function () {
    app(SystemPaymentGateway::class)->persist([
        'payment_vendor_id' => 'SYS-TILL',
        'payment_api_key' => 'sys-key',
        'payment_api_secret' => 'sys-secret',
        'payment_is_live' => '1',
    ]);

    $this->restaurantA->update([
        'selcom_vendor_id' => 'OLD-TILL',
        'selcom_api_key' => 'old-key',
        'selcom_api_secret' => 'old-secret',
    ]);

    $credentials = $this->restaurantA->getSelcomCredentials();

    expect($credentials['vendor_id'])->toBe('SYS-TILL')
        ->and($credentials['api_key'])->toBe('sys-key')
        ->and($credentials['is_live'])->toBeTrue();
});

test('wallet balance is per restaurant and excludes other restaurants', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 50000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-1',
    ]);

    Payment::query()->create([
        'restaurant_id' => $this->restaurantB->id,
        'amount' => 90000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PB-1',
    ]);

    $wallet = app(RestaurantWalletService::class);

    expect($wallet->totalEarned($this->restaurantA))->toBe(50000.0)
        ->and($wallet->totalEarned($this->restaurantB))->toBe(90000.0)
        ->and($wallet->availableBalance($this->restaurantA))->toBe(50000.0);
});

test('manager can view wallet and request withdrawal', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 25000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-2',
    ]);

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Wallet')
        ->assertSee('25,000');

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 10000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect();

    expect(Withdrawal::query()->where('restaurant_id', $this->restaurantA->id)->count())->toBe(1);

    $wallet = app(RestaurantWalletService::class);
    expect($wallet->availableBalance($this->restaurantA))->toBe(15000.0);
});

test('manager cannot withdraw more than available balance', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 5000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-3',
    ]);

    $this->actingAs($this->managerA)
        ->from(route('manager.wallet.index'))
        ->post(route('manager.wallet.store'), [
            'amount' => 8000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect(route('manager.wallet.index'))
        ->assertSessionHasErrors('amount');
});

test('manager selcom api routes are removed', function () {
    $this->actingAs($this->managerA)
        ->post('/manager/api/selcom')
        ->assertNotFound();
});

test('bot demo payment api tags payment to order restaurant only', function () {
    Setting::set('demo_push', '1', 'payments');

    $botUser = User::factory()->create();
    $botUser->assignRole('bot_service');
    $token = $botUser->createToken('bot-test')->plainTextToken;

    $orderA = Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => '1',
        'status' => 'pending',
        'total_amount' => 18000,
    ]);

    $orderB = Order::create([
        'restaurant_id' => $this->restaurantB->id,
        'table_number' => '2',
        'status' => 'pending',
        'total_amount' => 42000,
    ]);

    $this->withToken($token)
        ->postJson('/api/bot/payment/ussd', [
            'order_id' => $orderA->id,
            'phone_number' => '255700000001',
            'amount' => 18000,
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $this->withToken($token)
        ->postJson('/api/bot/payment/ussd', [
            'order_id' => $orderB->id,
            'phone_number' => '255700000002',
            'amount' => 42000,
        ])
        ->assertSuccessful();

    $wallet = app(RestaurantWalletService::class);

    expect(Payment::query()->where('restaurant_id', $this->restaurantA->id)->count())->toBe(1)
        ->and(Payment::query()->where('restaurant_id', $this->restaurantB->id)->count())->toBe(1)
        ->and($wallet->availableBalance($this->restaurantA))->toBe(18000.0)
        ->and($wallet->availableBalance($this->restaurantB))->toBe(42000.0);
});

test('manager B wallet does not include restaurant A earnings', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 75000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-A',
    ]);

    $this->actingAs($this->managerB)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Available balance')
        ->assertSee('0');

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('75,000');
});

test('wallet page shows withdraw ui and recent payments for own restaurant', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 12000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-UI-1',
    ]);

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Request withdrawal')
        ->assertSee('Submit withdrawal request')
        ->assertSee('Recent payments (this restaurant only)')
        ->assertSee('PA-UI-1')
        ->assertSee('12,000');
});

test('admin sees pending withdrawal from manager wallet request', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 30000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-WD',
    ]);

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 10000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect();

    $this->actingAs($this->admin)
        ->get(route('admin.withdrawals.index'))
        ->assertOk()
        ->assertSee('Restaurant A')
        ->assertSee('10,000')
        ->assertSee('pending');
});

test('manager can save payout profile and withdraw using saved details', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 25000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-PAYOUT',
    ]);

    $this->actingAs($this->managerA)
        ->put(route('manager.wallet.payout-profile.update'), [
            'payout_method' => 'Mobile Money',
            'payout_details' => '255712345678 · John Manager',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->restaurantA->refresh();

    expect($this->restaurantA->hasPayoutProfile())->toBeTrue();

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 10000,
            'use_saved_payout' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $withdrawal = Withdrawal::query()->where('restaurant_id', $this->restaurantA->id)->first();

    expect($withdrawal)->not->toBeNull()
        ->and($withdrawal->payment_method)->toBe('Mobile Money')
        ->and($withdrawal->payment_details)->toBe('255712345678 · John Manager');
});

test('second withdrawal fails when pending total exceeds available balance', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 20000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-LOCK',
    ]);

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 15000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->actingAs($this->managerA)
        ->from(route('manager.wallet.index'))
        ->post(route('manager.wallet.store'), [
            'amount' => 8000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect(route('manager.wallet.index'))
        ->assertSessionHasErrors('amount');

    expect(Withdrawal::query()->where('restaurant_id', $this->restaurantA->id)->count())->toBe(1);
});

test('admin withdrawal decision notifies restaurant manager', function () {
    Notification::fake();

    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 30000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-NOTIFY',
    ]);

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 10000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect();

    $withdrawal = Withdrawal::query()->where('restaurant_id', $this->restaurantA->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->post(route('admin.withdrawals.reject', $withdrawal->id), [
            'admin_note' => 'Wrong account details.',
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $this->managerA,
        \App\Notifications\WithdrawalStatusNotification::class,
        fn (\App\Notifications\WithdrawalStatusNotification $notification) => $notification->status === 'rejected'
    );
});

test('wallet page shows unread withdrawal status alerts', function () {
    Payment::query()->create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 30000,
        'method' => 'ussd',
        'status' => 'paid',
        'transaction_reference' => 'PA-ALERT',
    ]);

    $this->actingAs($this->managerA)
        ->post(route('manager.wallet.store'), [
            'amount' => 10000,
            'payment_method' => 'Mobile Money',
            'payment_details' => '255712345678',
        ])
        ->assertRedirect();

    $withdrawal = Withdrawal::query()->where('restaurant_id', $this->restaurantA->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->post(route('admin.withdrawals.reject', $withdrawal->id), [
            'admin_note' => 'Wrong account details.',
        ])
        ->assertRedirect();

    $this->actingAs($this->managerA)
        ->get(route('manager.wallet.index'))
        ->assertOk()
        ->assertSee('Withdrawal updates')
        ->assertSee('Wrong account details.');
});

test('manager dashboard revenue reflects paid orders for own restaurant only', function () {
    Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => '3',
        'status' => 'paid',
        'total_amount' => 22000,
        'created_at' => now(),
    ]);

    Order::create([
        'restaurant_id' => $this->restaurantB->id,
        'table_number' => '4',
        'status' => 'paid',
        'total_amount' => 99000,
        'created_at' => now(),
    ]);

    $statsA = $this->actingAs($this->managerA)
        ->getJson(route('manager.dashboard.stats'))
        ->assertOk()
        ->json();

    $statsB = $this->actingAs($this->managerB)
        ->getJson(route('manager.dashboard.stats'))
        ->assertOk()
        ->json();

    expect((float) $statsA['revenue_today'])->toBe(22000.0)
        ->and((float) $statsB['revenue_today'])->toBe(99000.0);
});
