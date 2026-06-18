<?php

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Enums\BotQrEntryType;
use App\Models\BotEvent;
use App\Models\BotSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->restaurantA = Restaurant::create([
        'name' => 'Alpha Grill',
        'location' => 'Johannesburg',
        'phone' => '0800000001',
        'is_active' => true,
    ]);

    $this->restaurantB = Restaurant::create([
        'name' => 'Beta Bistro',
        'location' => 'Cape Town',
        'phone' => '0800000002',
        'is_active' => false,
    ]);

    $this->manager->update(['restaurant_id' => $this->restaurantA->id]);
});

test('super admin can fetch tiptap analysis platform snapshot', function (): void {
    $orderToday = Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => 'T1',
        'status' => 'completed',
        'total_amount' => 20000,
        'created_at' => now(),
    ]);

    $oldOrder = Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => 'T2',
        'status' => 'completed',
        'total_amount' => 10000,
    ]);
    $oldOrder->forceFill([
        'created_at' => now()->subMonths(2),
        'updated_at' => now()->subMonths(2),
    ])->save();

    Payment::create([
        'order_id' => $orderToday->id,
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 20000,
        'method' => 'cash',
        'status' => 'paid',
        'created_at' => now(),
    ]);

    Payment::create([
        'order_id' => $orderToday->id,
        'restaurant_id' => $this->restaurantB->id,
        'amount' => 5000,
        'method' => 'ussd',
        'status' => 'completed',
        'created_at' => now()->subDays(3),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.snapshot', [
        'trend_days' => 7,
    ]));

    $response->assertOk()
        ->assertJsonPath('snapshot.restaurants.active', 1)
        ->assertJsonPath('snapshot.restaurants.inactive', 1)
        ->assertJsonPath('snapshot.restaurants.total', 2)
        ->assertJsonPath('snapshot.orders.today', 1)
        ->assertJsonPath('snapshot.orders.week', 1)
        ->assertJsonPath('snapshot.orders.month', 1)
        ->assertJsonPath('snapshot.filters.trend_days', 7);

    expect($response->json('snapshot.revenue_trend'))->toHaveCount(7);
    expect($response->json('snapshot.top_restaurants.0.name'))->toBe('Alpha Grill');
    expect($response->json('snapshot.top_restaurants.0.revenue'))->toEqual(20000);
});

test('platform snapshot can be filtered by restaurant', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => 'T1',
        'status' => 'pending',
        'total_amount' => 1000,
        'created_at' => now(),
    ]);

    Order::create([
        'restaurant_id' => $this->restaurantB->id,
        'table_number' => 'T9',
        'status' => 'pending',
        'total_amount' => 1000,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.snapshot', [
        'restaurant_id' => $this->restaurantA->id,
    ]));

    $response->assertOk()
        ->assertJsonPath('snapshot.orders.today', 1)
        ->assertJsonPath('snapshot.filters.restaurant_id', $this->restaurantA->id);
});

test('super admin can fetch whatsapp engagement analytics', function (): void {
    BotEvent::create([
        'wa_id' => '255712345678',
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::ViewMenu->value,
        'occurred_at' => now(),
    ]);

    BotEvent::create([
        'wa_id' => '255712345678',
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::ViewMenu->value,
        'occurred_at' => now()->subDay(),
    ]);

    BotEvent::create([
        'wa_id' => '255798765432',
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::CallWaiter->value,
        'occurred_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.whatsapp-engagement', [
        'days' => 7,
    ]));

    $response->assertOk()
        ->assertJsonPath('whatsapp_engagement.total_events', 3)
        ->assertJsonPath('whatsapp_engagement.filters.days', 7);

    $usage = collect($response->json('whatsapp_engagement.option_usage'));
    expect($usage->firstWhere('key', 'view_menu')['value'])->toBe(2);
    expect($usage->firstWhere('key', 'call_waiter')['value'])->toBe(1);
    expect($usage->firstWhere('key', 'pay_bill')['value'])->toBe(0);
    expect($response->json('whatsapp_engagement.daily_trend'))->toHaveCount(7);
});

test('whatsapp engagement analytics can be filtered by restaurant', function (): void {
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::ExitBot->value,
        'occurred_at' => now(),
    ]);

    BotEvent::create([
        'restaurant_id' => $this->restaurantB->id,
        'event_type' => BotEngagementEvent::ExitBot->value,
        'occurred_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.whatsapp-engagement', [
        'restaurant_id' => $this->restaurantA->id,
        'days' => 30,
    ]));

    $response->assertOk()
        ->assertJsonPath('whatsapp_engagement.total_events', 1)
        ->assertJsonPath('whatsapp_engagement.filters.restaurant_id', $this->restaurantA->id);
});

test('manager cannot access tiptap analysis apis', function (): void {
    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.snapshot'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.whatsapp-engagement'))
        ->assertForbidden();
});

test('guest cannot access tiptap analysis apis', function (): void {
    $this->getJson(route('admin.tiptap-analysis.snapshot'))
        ->assertUnauthorized();

    $this->getJson(route('admin.tiptap-analysis.whatsapp-engagement'))
        ->assertUnauthorized();
});

test('super admin can fetch qr entry point analytics', function (): void {
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotQrEntryType::Waiter->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotQrEntryType::Table->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantB->id,
        'event_type' => BotQrEntryType::Restaurant->value,
        'occurred_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.qr-entry-points', [
        'days' => 30,
    ]));

    $response->assertOk()
        ->assertJsonPath('qr_entry_points.total_scans', 3);

    $split = collect($response->json('qr_entry_points.split'));
    expect($split->firstWhere('key', 'qr_waiter')['value'])->toBe(1);
    expect($split->firstWhere('key', 'qr_table')['value'])->toBe(1);

    $insights = $response->json('qr_entry_points.per_restaurant');
    expect($insights)->not->toBeEmpty();
    expect($insights[0])->toHaveKeys(['name', 'preferred', 'insight']);
});

test('super admin can fetch customer journey funnel analytics', function (): void {
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotQrEntryType::Waiter->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotFunnelStep::BotHome->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotFunnelStep::ViewMenu->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotFunnelStep::ConfirmOrder->value,
        'occurred_at' => now(),
    ]);
    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotFunnelStep::PayBill->value,
        'occurred_at' => now(),
    ]);

    $order = Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => 'T1',
        'status' => 'paid',
        'total_amount' => 10000,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 10000,
        'method' => 'cash',
        'status' => 'paid',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 5000,
        'method' => 'ussd',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.customer-journey'));

    $response->assertOk();
    $steps = collect($response->json('customer_journey.steps'));
    expect($steps->firstWhere('key', 'qr_scan')['count'])->toBe(1);
    expect($steps->firstWhere('key', 'confirm_order')['count'])->toBe(1);

    $paymentMethods = collect($response->json('customer_journey.payment_methods'));
    expect($paymentMethods->firstWhere('label', 'Cash')['value'])->toBe(1);
    expect($paymentMethods->firstWhere('label', 'Digital')['value'])->toBe(1);
});

test('super admin can fetch feedback overview analytics', function (): void {
    \App\Models\Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantA->id,
        'type' => 'waiter',
        'rating' => 5,
        'comment' => 'Excellent waiter service',
    ]);

    \App\Models\Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantA->id,
        'type' => 'food',
        'rating' => 2,
        'comment' => 'Food was cold',
    ]);

    \App\Models\Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantB->id,
        'type' => 'restaurant',
        'rating' => 3,
        'comment' => 'Average ambience',
    ]);

    \App\Models\Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantB->id,
        'type' => 'food',
        'rating' => 3,
        'comment' => 'Okay food',
    ]);

    \App\Models\Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantB->id,
        'type' => 'waiter',
        'rating' => 4,
        'comment' => 'Waiter was fine',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.feedback-overview', [
        'days' => 30,
    ]));

    $response->assertOk()
        ->assertJsonPath('feedback_overview.summary.total_reviews', 5)
        ->assertJsonPath('feedback_overview.summary.avg_rating', 3.4);

    $byType = collect($response->json('feedback_overview.by_type'));
    expect($byType->firstWhere('key', 'waiter')['count'])->toBe(2);
    expect($byType->firstWhere('key', 'food')['count'])->toBe(2);

    expect($response->json('feedback_overview.recent_comments'))->toHaveCount(5);
    expect($response->json('feedback_overview.recent_comments.0'))->not->toHaveKey('customer_phone');

    $alerts = $response->json('feedback_overview.low_rating_alerts');
    expect(collect($alerts)->pluck('name'))->toContain('Beta Bistro');
});

test('manager cannot access new tiptap analysis section apis', function (): void {
    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.qr-entry-points'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.customer-journey'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.feedback-overview'))
        ->assertForbidden();
});

test('super admin can fetch tips and payments analytics', function (): void {
    $waiter = User::factory()->create(['restaurant_id' => $this->restaurantA->id]);
    $waiter->assignRole('waiter');

    \App\Models\Tip::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantA->id,
        'waiter_id' => $waiter->id,
        'amount' => 5000,
    ]);

    \App\Models\Tip::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurantA->id,
        'waiter_id' => $waiter->id,
        'amount' => 3000,
    ]);

    $order = Order::create([
        'restaurant_id' => $this->restaurantA->id,
        'table_number' => 'T1',
        'status' => 'paid',
        'total_amount' => 20000,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 20000,
        'method' => 'cash',
        'payment_type' => 'order',
        'status' => 'paid',
    ]);

    Payment::create([
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 7000,
        'method' => 'ussd',
        'payment_type' => 'quick',
        'status' => 'completed',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'restaurant_id' => $this->restaurantA->id,
        'amount' => 4000,
        'method' => 'card',
        'payment_type' => 'order',
        'status' => 'paid',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.tips-and-payments'));

    $response->assertOk()
        ->assertJsonPath('tips_and_payments.tips.total_amount', 8000)
        ->assertJsonPath('tips_and_payments.tips.avg_amount', 4000)
        ->assertJsonPath('tips_and_payments.tips.count', 2);

    $methods = collect($response->json('tips_and_payments.payment_methods'));
    expect($methods->firstWhere('key', 'cash')['value'])->toBe(1);
    expect($methods->firstWhere('key', 'ussd')['value'])->toBe(1);
    expect($methods->firstWhere('key', 'card')['value'])->toBe(1);

    $purpose = collect($response->json('tips_and_payments.payment_purpose'));
    expect($purpose->firstWhere('key', 'order')['value'])->toBe(2);
    expect($purpose->firstWhere('key', 'quick')['value'])->toBe(1);
    expect($response->json('tips_and_payments.top_tipped_waiters.0.name'))->toBe($waiter->name);
});

test('super admin can fetch language and behavior analytics', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'sw',
        'data' => ['restaurant_id' => $this->restaurantA->id],
        'last_message_at' => now()->setHour(19),
    ]);

    BotSession::create([
        'wa_id' => '255798765432',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => ['restaurant_id' => $this->restaurantA->id],
        'last_message_at' => now()->setHour(20),
    ]);

    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::ViewMenu->value,
        'occurred_at' => now()->setHour(19)->setMinutes(15),
    ]);

    BotEvent::create([
        'restaurant_id' => $this->restaurantA->id,
        'event_type' => BotEngagementEvent::CallWaiter->value,
        'occurred_at' => now()->setHour(19)->setMinutes(45),
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.language-and-behavior'));

    $response->assertOk();

    $split = collect($response->json('language_and_behavior.language_split'));
    expect($split->firstWhere('key', 'sw')['value'])->toBe(1);
    expect($split->firstWhere('key', 'en')['value'])->toBe(1);

    $perRestaurant = $response->json('language_and_behavior.per_restaurant');
    expect($perRestaurant[0]['name'])->toBe('Alpha Grill');
    expect($perRestaurant[0]['preferred'])->toBeIn(['en', 'sw']);

    expect($response->json('language_and_behavior.peak_hours.peak_event_hour'))->toBe(19);
    expect(collect($response->json('language_and_behavior.peak_hours.events')))->toHaveCount(24);
});

test('manager cannot access tips and language analysis apis', function (): void {
    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.tips-and-payments'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->getJson(route('admin.tiptap-analysis.language-and-behavior'))
        ->assertForbidden();
});

test('super admin can view tiptap analysis page', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.tiptap-analysis.index'))
        ->assertOk()
        ->assertSee('TipTap Analysis')
        ->assertSee('Platform')
        ->assertSee('Open report');
});

test('super admin can view tiptap analysis platform section page', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.tiptap-analysis.platform'))
        ->assertOk()
        ->assertSee('Platform snapshot')
        ->assertSee('Back to TipTap Analysis')
        ->assertSee('Revenue trend');
});

test('manager cannot view tiptap analysis page', function (): void {
    $this->actingAs($this->manager)
        ->get(route('admin.tiptap-analysis.index'))
        ->assertForbidden();
});

test('manager cannot view tiptap analysis section pages', function (): void {
    $this->actingAs($this->manager)
        ->get(route('admin.tiptap-analysis.journey'))
        ->assertForbidden();
});

test('tiptap analysis sample seeder populates dashboard data', function (): void {
    $this->seed(\Database\Seeders\TiptapAnalysisSampleSeeder::class);

    expect(\App\Models\BotEvent::query()->where('metadata->seed', \Database\Seeders\TiptapAnalysisSampleSeeder::MARKER)->count())
        ->toBeGreaterThan(100);

    $response = $this->actingAs($this->admin)->getJson(route('admin.tiptap-analysis.snapshot', ['trend_days' => 30]));

    $response->assertOk();
    expect($response->json('snapshot.orders.month'))->toBeGreaterThan(0);
    expect($response->json('snapshot.top_restaurants'))->not->toBeEmpty();
});
