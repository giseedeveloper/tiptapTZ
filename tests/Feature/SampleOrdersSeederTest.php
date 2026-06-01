<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SampleOrdersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sample orders seeder creates orders payments and menu for demo restaurant', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $restaurant = Restaurant::create([
        'name' => 'TAPTAP Demo Grill',
        'location' => 'Dar es Salaam',
        'phone' => '0700000000',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $waiter->assignRole('waiter');

    $this->seed(SampleOrdersSeeder::class);

    expect(Order::withoutGlobalScopes()->where('notes', 'tiptap_sample_seed')->count())->toBeGreaterThan(10);
    expect(Payment::query()->whereIn('status', ['paid', 'completed'])->count())->toBeGreaterThan(3);

    $this->seed(SampleOrdersSeeder::class);

    $countAfterSecondRun = Order::withoutGlobalScopes()->where('notes', 'tiptap_sample_seed')->count();
    expect($countAfterSecondRun)->toBeGreaterThan(10);
});
