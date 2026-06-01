<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleOrdersSeeder extends Seeder
{
    private const MARKER = 'tiptap_sample_seed';

    public function run(): void
    {
        if (Order::withoutGlobalScopes()->where('notes', self::MARKER)->exists()) {
            $this->command?->info('Sample orders already exist (notes: '.self::MARKER.'). Skipping.');

            return;
        }

        $restaurant = Restaurant::query()->where('name', 'TAPTAP Demo Grill')->first()
            ?? Restaurant::query()->where('is_active', true)->first();

        if (! $restaurant) {
            $this->command?->warn('No restaurant found. Run DatabaseSeeder first.');

            return;
        }

        $waiter = User::role('waiter')
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $waiter) {
            $this->command?->warn('No waiter found for restaurant. Run DatabaseSeeder first.');

            return;
        }

        $category = Category::withoutGlobalScopes()->firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Sample Menu'],
            ['sort_order' => 1],
        );

        $menuItems = $this->ensureMenuItems($restaurant->id, $category->id);

        $statusRotation = ['pending', 'preparing', 'ready', 'served', 'paid', 'completed', 'paid', 'preparing'];
        $paymentMethods = ['cash', 'mobile', 'ussd', 'cash', 'mobile'];
        $tables = ['T1', 'T2', 'T3', 'T4', 'VIP-1', 'Bar-2'];
        $customers = ['Asha M.', 'John K.', 'Neema P.', 'David L.', 'WhatsApp Guest'];

        $orderCount = 0;

        for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
            $day = now()->subDays($dayOffset)->startOfDay();
            $ordersToday = random_int(2, 5);

            for ($n = 0; $n < $ordersToday; $n++) {
                $status = $statusRotation[($orderCount + $dayOffset) % count($statusRotation)];
                $placedAt = $day->copy()->addHours(random_int(10, 21))->addMinutes(random_int(0, 55));

                $lineItems = $this->pickLineItems($menuItems, random_int(1, 3));
                $total = collect($lineItems)->sum('total');

                $order = Order::withoutGlobalScopes()->create([
                    'restaurant_id' => $restaurant->id,
                    'waiter_id' => $waiter->id,
                    'table_number' => $tables[$orderCount % count($tables)],
                    'customer_name' => $customers[$orderCount % count($customers)],
                    'customer_phone' => '2557'.str_pad((string) (700000000 + $orderCount), 9, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'total_amount' => $total,
                    'notes' => self::MARKER,
                ]);

                $order->created_at = $placedAt;
                $order->updated_at = $placedAt->copy()->addMinutes(random_int(5, 90));
                $order->saveQuietly();

                foreach ($lineItems as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $line['menu_item_id'],
                        'name' => $line['name'],
                        'quantity' => $line['quantity'],
                        'price' => $line['price'],
                        'total' => $line['total'],
                        'status' => in_array($status, ['paid', 'completed', 'served'], true) ? 'served' : 'pending',
                    ]);
                }

                if (in_array($status, ['paid', 'completed'], true)) {
                    $paidAt = $placedAt->copy()->addMinutes(random_int(15, 120));
                    Payment::create([
                        'order_id' => $order->id,
                        'restaurant_id' => $restaurant->id,
                        'waiter_id' => $waiter->id,
                        'customer_phone' => $order->customer_phone,
                        'amount' => $total,
                        'method' => $paymentMethods[$orderCount % count($paymentMethods)],
                        'status' => $status === 'completed' ? 'completed' : 'paid',
                        'transaction_reference' => 'SAMPLE-'.strtoupper(substr(md5((string) $order->id), 0, 8)),
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);
                }

                if ($orderCount % 4 === 0 && in_array($status, ['paid', 'completed', 'served'], true)) {
                    Feedback::withoutGlobalScopes()->create([
                        'restaurant_id' => $restaurant->id,
                        'order_id' => $order->id,
                        'waiter_id' => $waiter->id,
                        'rating' => random_int(3, 5),
                        'comment' => 'Sample feedback for dashboard charts.',
                        'created_at' => $order->updated_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }

                if ($orderCount % 5 === 0 && in_array($status, ['paid', 'completed'], true)) {
                    Tip::withoutGlobalScopes()->create([
                        'restaurant_id' => $restaurant->id,
                        'order_id' => $order->id,
                        'waiter_id' => $waiter->id,
                        'amount' => (int) round($total * 0.05),
                        'created_at' => $order->updated_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }

                $orderCount++;
            }
        }

        $this->command?->info("Seeded {$orderCount} sample orders for «{$restaurant->name}» (last 7 days).");
    }

    /**
     * @return array<int, MenuItem>
     */
    private function ensureMenuItems(int $restaurantId, int $categoryId): array
    {
        $definitions = [
            ['name' => 'Grilled Tilapia', 'price' => 18000],
            ['name' => 'Pilau Rice', 'price' => 12000],
            ['name' => 'Mishkaki Platter', 'price' => 15000],
            ['name' => 'Fresh Juice', 'price' => 5000],
            ['name' => 'Ugali & Nyama', 'price' => 22000],
        ];

        $items = [];

        foreach ($definitions as $sort => $def) {
            $items[] = MenuItem::withoutGlobalScopes()->firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'name' => $def['name'],
                ],
                [
                    'category_id' => $categoryId,
                    'description' => 'Sample menu item for demo data.',
                    'price' => $def['price'],
                    'is_available' => true,
                ],
            );
        }

        return $items;
    }

    /**
     * @param  array<int, MenuItem>  $menuItems
     * @return array<int, array{menu_item_id: int, name: string, quantity: int, price: float, total: float}>
     */
    private function pickLineItems(array $menuItems, int $count): array
    {
        $picked = collect($menuItems)->random(min($count, count($menuItems)));
        $lines = [];

        foreach ($picked as $item) {
            $qty = random_int(1, 2);
            $lines[] = [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'quantity' => $qty,
                'price' => (float) $item->price,
                'total' => (float) $item->price * $qty,
            ];
        }

        return $lines;
    }
}
