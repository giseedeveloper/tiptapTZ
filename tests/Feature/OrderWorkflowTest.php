<?php

use App\Models\Order;
use App\Models\OrderStatusEvent;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Workflow Rest',
        'location' => 'Dar',
        'phone' => '0700111000',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->workflow = app(OrderWorkflowService::class);
});

function makeOrder(array $overrides = []): Order
{
    return Order::withoutGlobalScopes()->create(array_merge([
        'restaurant_id' => test()->restaurant->id,
        'table_number' => 'T1',
        'customer_phone' => '255700000001',
        'status' => OrderWorkflow::RECEIVED,
        'total_amount' => 50,
    ], $overrides));
}

it('normalizes legacy pending and paid statuses on create', function (): void {
    $pending = makeOrder(['status' => 'pending']);
    $paid = makeOrder(['status' => 'paid']);

    expect($pending->fresh()->status)->toBe(OrderWorkflow::RECEIVED)
        ->and($pending->received_at)->not->toBeNull()
        ->and($paid->fresh()->status)->toBe(OrderWorkflow::COMPLETED);
});

it('walks the full received to completed pipeline with timestamps and events', function (): void {
    $order = makeOrder();
    $this->workflow->markReceived($order, $this->manager, 'test');

    $path = [
        OrderWorkflow::ACCEPTED,
        OrderWorkflow::PREPARING,
        OrderWorkflow::READY,
        OrderWorkflow::SERVED,
        OrderWorkflow::COMPLETED,
    ];

    foreach ($path as $status) {
        $this->workflow->transition($order->fresh(), $status, $this->manager, 'test');
    }

    $order->refresh();

    expect($order->status)->toBe(OrderWorkflow::COMPLETED)
        ->and($order->received_at)->not->toBeNull()
        ->and($order->accepted_at)->not->toBeNull()
        ->and($order->preparing_at)->not->toBeNull()
        ->and($order->ready_at)->not->toBeNull()
        ->and($order->served_at)->not->toBeNull()
        ->and($order->completed_at)->not->toBeNull();

    $events = OrderStatusEvent::query()->where('order_id', $order->id)->orderBy('id')->get();
    expect($events->count())->toBeGreaterThanOrEqual(6)
        ->and($events->last()->to_status)->toBe(OrderWorkflow::COMPLETED);
});

it('rejects backward workflow transitions', function (): void {
    $order = makeOrder(['status' => OrderWorkflow::PREPARING, 'preparing_at' => now()]);

    expect(fn () => $this->workflow->transition($order, OrderWorkflow::RECEIVED, $this->manager, 'test'))
        ->toThrow(ValidationException::class);
});

it('completes order from payment without changing payment table semantics', function (): void {
    $order = makeOrder(['status' => OrderWorkflow::SERVED, 'served_at' => now()]);

    $this->workflow->completeFromPayment($order, 'test_payment');

    expect($order->fresh()->status)->toBe(OrderWorkflow::COMPLETED)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

it('lets manager advance live board through the workflow UI routes', function (): void {
    $order = makeOrder();

    $this->actingAs($this->manager)
        ->put(route('manager.orders.update', $order), ['status' => 'accepted'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderWorkflow::ACCEPTED);

    $this->actingAs($this->manager)
        ->put(route('manager.orders.update', $order), ['status' => 'preparing'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderWorkflow::PREPARING);

    $response = $this->actingAs($this->manager)->get(route('manager.orders.live'));
    $response->assertOk()
        ->assertSee('Received', false)
        ->assertSee('Accepted', false)
        ->assertSee('Ready', false)
        ->assertSee('Completed', false)
        ->assertSee('in stage', false);
});

it('exposes stage times and bottlenecks on the manager dashboard', function (): void {
    $order = makeOrder();
    $this->workflow->markReceived($order, $this->manager, 'test');

    // Create slow "received" dwell via synthetic events.
    for ($i = 0; $i < 3; $i++) {
        $o = makeOrder();
        OrderStatusEvent::create([
            'order_id' => $o->id,
            'restaurant_id' => $this->restaurant->id,
            'from_status' => OrderWorkflow::RECEIVED,
            'to_status' => OrderWorkflow::ACCEPTED,
            'changed_by' => $this->manager->id,
            'source' => 'test',
            'duration_seconds' => 20 * 60, // 20 min > 5 min threshold
        ]);
        $this->workflow->transition($o, OrderWorkflow::ACCEPTED, $this->manager, 'test');
    }

    $metrics = $this->workflow->dashboardMetrics($this->restaurant->id);

    expect($metrics['segments'])->toHaveCount(6)
        ->and($metrics['stage_times'])->not->toBeEmpty()
        ->and(collect($metrics['bottlenecks'])->pluck('key')->all())->toContain(OrderWorkflow::RECEIVED);

    $this->actingAs($this->manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Time spent by stage', false)
        ->assertSee('Bottlenecks', false);
});

it('accepts legacy paid status on API update and stores completed', function (): void {
    $order = makeOrder(['status' => OrderWorkflow::SERVED, 'served_at' => now()]);

    $this->actingAs($this->manager, 'sanctum')
        ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'paid'])
        ->assertOk();

    expect($order->fresh()->status)->toBe(OrderWorkflow::COMPLETED);
});
