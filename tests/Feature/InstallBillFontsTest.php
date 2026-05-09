<?php

use App\Models\Order;
use App\Models\Restaurant;
use App\Services\BillImageService;
use Illuminate\Support\Facades\File;

it('copies DejaVu fonts into resources/fonts', function (): void {
    $vendorSans = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
    if (! is_file($vendorSans)) {
        $this->markTestSkipped('Dompdf vendor fonts not present.');
    }

    $destDir = resource_path('fonts');
    foreach (['DejaVuSans.ttf', 'DejaVuSans-Bold.ttf'] as $file) {
        $path = $destDir.'/'.$file;
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    $this->artisan('bill:install-fonts', ['--force' => true])
        ->assertSuccessful();

    expect(is_readable($destDir.'/DejaVuSans.ttf'))->toBeTrue();
    expect(is_readable($destDir.'/DejaVuSans-Bold.ttf'))->toBeTrue();
});

it('renders a valid bill PNG via BillImageService', function (): void {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension not loaded.');
    }

    $restaurant = Restaurant::create([
        'name' => 'Font Test Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '3',
        'customer_phone' => '255700000001',
        'status' => 'served',
        'total_amount' => 50,
    ]);

    $png = app(BillImageService::class)->renderPng($order);

    expect($png)->not->toBeEmpty();
    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});
