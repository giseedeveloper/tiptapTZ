<?php

use App\Models\DailyReport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Tip;
use App\Models\User;
use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    Storage::fake('local');

    $this->restaurant = Restaurant::create([
        'name' => 'Daily Report Rest',
        'location' => 'Dar',
        'phone' => '0700111222',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Amina Waiter',
    ]);
    $this->waiter->assignRole('waiter');

    Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'T1',
        'is_active' => true,
    ]);
    Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'T2',
        'is_active' => true,
    ]);

    $day = Carbon::parse('2026-07-12 19:30:00');
    $this->reportDate = $day->copy()->startOfDay();

    $order1 = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_id' => $this->waiter->id,
        'table_number' => 'T1',
        'customer_phone' => '255700000001',
        'status' => 'paid',
        'total_amount' => 100,
    ]);
    $order1->forceFill([
        'created_at' => $day,
        'updated_at' => $day,
    ])->saveQuietly();
    OrderItem::query()->create([
        'order_id' => $order1->id,
        'name' => 'Nyama Choma',
        'quantity' => 2,
        'price' => 40,
        'total' => 80,
    ]);

    $order2 = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_id' => $this->waiter->id,
        'table_number' => 'T2',
        'customer_phone' => '255700000002',
        'status' => 'served',
        'total_amount' => 50,
    ]);
    $order2->forceFill([
        'created_at' => $day->copy()->setTime(12, 0),
        'updated_at' => $day->copy()->setTime(12, 0),
    ])->saveQuietly();
    OrderItem::query()->create([
        'order_id' => $order2->id,
        'name' => 'Chips',
        'quantity' => 1,
        'price' => 50,
        'total' => 50,
    ]);

    // Returning customer: ordered before and again today
    $prior = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_id' => $this->waiter->id,
        'table_number' => 'T1',
        'customer_phone' => '255700000001',
        'status' => 'paid',
        'total_amount' => 20,
    ]);
    $prior->forceFill([
        'created_at' => $day->copy()->subDays(3),
        'updated_at' => $day->copy()->subDays(3),
    ])->saveQuietly();

    $payment1 = Payment::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'order_id' => $order1->id,
        'waiter_id' => $this->waiter->id,
        'amount' => 100,
        'method' => 'ussd',
        'payment_type' => 'order',
        'status' => 'paid',
        'transaction_reference' => 'DR-1',
    ]);
    $payment1->forceFill([
        'created_at' => $day,
        'updated_at' => $day,
    ])->saveQuietly();

    $payment2 = Payment::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'order_id' => $order2->id,
        'waiter_id' => $this->waiter->id,
        'amount' => 50,
        'method' => 'ussd',
        'payment_type' => 'order',
        'status' => 'completed',
        'transaction_reference' => 'DR-2',
    ]);
    $payment2->forceFill([
        'created_at' => $day->copy()->setTime(12, 5),
        'updated_at' => $day->copy()->setTime(12, 5),
    ])->saveQuietly();

    $tip = Tip::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_id' => $this->waiter->id,
        'order_id' => $order1->id,
        'amount' => 10,
    ]);
    $tip->forceFill([
        'created_at' => $day,
        'updated_at' => $day,
    ])->saveQuietly();

    $this->token = $this->manager->createToken('daily-report-api')->plainTextToken;
});

test('daily report metrics include required business KPIs', function (): void {
    $metrics = app(DailyReportService::class)->buildMetrics($this->restaurant, $this->reportDate);

    expect($metrics['orders']['total'])->toBe(2)
        ->and($metrics['revenue']['total'])->toBe(150.0)
        ->and($metrics['aov'])->toBe(75.0)
        ->and($metrics['customers']['unique'])->toBe(2)
        ->and($metrics['customers']['returning'])->toBe(1)
        ->and($metrics['customers']['new'])->toBe(1)
        ->and($metrics['items'])->not->toBeEmpty()
        ->and($metrics['waiter_performance'][0]['name'])->toBe('Amina Waiter')
        ->and($metrics['waiter_performance'][0]['orders'])->toBe(2)
        ->and($metrics['waiter_performance'][0]['tips'])->toBe(10.0)
        ->and($metrics['turnover']['completed_turns'])->toBe(2)
        ->and($metrics['turnover']['active_tables'])->toBe(2)
        ->and($metrics['turnover']['rate'])->toBe(1.0)
        ->and($metrics['peak_hour']['hour'])->toBe(19)
        ->and($metrics['peak_hour']['orders'])->toBe(1);
});

test('generate creates pdf and excel daily report files', function (): void {
    $report = app(DailyReportService::class)->generate(
        $this->restaurant,
        $this->reportDate,
        DailyReport::SOURCE_MANUAL,
        true,
    );

    expect($report->hasPdf())->toBeTrue()
        ->and($report->hasExcel())->toBeTrue()
        ->and(Storage::disk('local')->exists($report->pdf_path))->toBeTrue()
        ->and(Storage::disk('local')->exists($report->excel_path))->toBeTrue();

    $excel = Storage::disk('local')->get($report->excel_path);
    expect(substr($excel, 0, 2))->toBe('PK'); // zip/xlsx signature

    $pdf = Storage::disk('local')->get($report->pdf_path);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue();
});

test('manager can view daily reports UI and generate exports', function (): void {
    $this->actingAs($this->manager)
        ->get(route('manager.reports.daily', ['date' => $this->reportDate->toDateString()]))
        ->assertOk()
        ->assertSee('Daily business reports')
        ->assertSee('AOV')
        ->assertSee('Turnover rate')
        ->assertSee('Peak hour');

    $this->actingAs($this->manager)
        ->post(route('manager.reports.daily.generate'), [
            'date' => $this->reportDate->toDateString(),
            'force' => 1,
        ])
        ->assertRedirect(route('manager.reports.daily', ['date' => $this->reportDate->toDateString()]));

    expect(DailyReport::query()
        ->where('restaurant_id', $this->restaurant->id)
        ->whereDate('report_date', $this->reportDate)
        ->exists())->toBeTrue();

    $this->actingAs($this->manager)
        ->get(route('manager.reports.daily.download', [
            'date' => $this->reportDate->toDateString(),
            'format' => 'pdf',
        ]))
        ->assertOk();

    $this->actingAs($this->manager)
        ->get(route('manager.reports.daily.download', [
            'date' => $this->reportDate->toDateString(),
            'format' => 'excel',
        ]))
        ->assertOk();
});

test('manager api can list generate and export daily reports', function (): void {
    $this->withToken($this->token)
        ->postJson('/api/v1/manager/daily-reports/generate', [
            'date' => $this->reportDate->toDateString(),
            'force' => true,
        ])
        ->assertOk()
        ->assertJsonPath('report.has_pdf', true)
        ->assertJsonPath('report.has_excel', true)
        ->assertJsonPath('report.metrics.orders.total', 2)
        ->assertJsonPath('report.metrics.aov', 75);

    $this->withToken($this->token)
        ->getJson('/api/v1/manager/daily-reports')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->withToken($this->token)
        ->getJson('/api/v1/manager/daily-reports/'.$this->reportDate->toDateString())
        ->assertOk()
        ->assertJsonPath('metrics.revenue.total', 150);

    $this->withToken($this->token)
        ->get('/api/v1/manager/daily-reports/'.$this->reportDate->toDateString().'/export/pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->withToken($this->token)
        ->get('/api/v1/manager/daily-reports/'.$this->reportDate->toDateString().'/export/excel')
        ->assertOk();
});

test('scheduled daily-reports generate command auto-creates yesterday report', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-07-13 00:20:00'));

    Artisan::call('daily-reports:generate', [
        '--restaurant' => $this->restaurant->id,
        '--force' => true,
    ]);

    expect(DailyReport::query()
        ->where('restaurant_id', $this->restaurant->id)
        ->whereDate('report_date', '2026-07-12')
        ->where('generation_source', DailyReport::SOURCE_SCHEDULED)
        ->exists())->toBeTrue();

    Carbon::setTestNow();
});
