<?php

namespace Database\Seeders;

use App\Enums\FeedbackType;
use App\Models\Category;
use App\Models\CustomerRequest;
use App\Models\Feedback;
use App\Models\MenuEngagementSession;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\TableZone;
use App\Models\Tip;
use App\Models\User;
use App\Models\WaiterRestaurantAssignment;
use App\Models\WaiterSalaryPayment;
use App\Models\WaiterShift;
use App\Support\OrderWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class TrialRestaurantDemoSeeder extends Seeder
{
    private const MARKER = 'tiptap_trial_visual_demo_v1';

    public function run(): void
    {
        $restaurant = Restaurant::query()
            ->whereRaw('LOWER(name) = ?', ['trial'])
            ->first();

        $manager = User::query()
            ->whereRaw('LOWER(email) = ?', ['trial@gmail.com'])
            ->first();

        if (! $restaurant || ! $manager || (int) $manager->restaurant_id !== (int) $restaurant->id) {
            throw new RuntimeException('The TZ trial manager/restaurant pair was not found. No data was changed.');
        }

        DB::transaction(function () use ($restaurant, $manager): void {
            $restaurant->forceFill([
                'is_active' => true,
                'approval_status' => Restaurant::STATUS_ACTIVE,
                'menu_engagement_alerts_enabled' => true,
                'menu_engagement_timeout_minutes' => 10,
            ])->save();

            $waiters = $this->ensureWaiters($restaurant);
            $supervisor = $this->ensureSupervisor($restaurant);
            $zones = $this->ensureZones($restaurant, $supervisor);
            $tables = $this->ensureTables($restaurant, $zones, $waiters);
            $menuItems = $this->ensureMenu($restaurant);

            $this->ensureAssignments($restaurant, $waiters);
            $this->ensureRoster($restaurant, $manager, $waiters);
            $this->ensurePayroll($restaurant, $manager, $waiters);

            $orders = $this->ensureOrders($restaurant, $waiters, $tables, $menuItems);

            $this->ensureCustomerRequests($restaurant, $waiters, $tables);
            $this->ensureMenuEngagement($restaurant, $tables);
            $this->ensureFeedbackAndTips($restaurant, $orders);
        });

        $counts = [
            'waiters' => User::role('waiter')->where('restaurant_id', $restaurant->id)->count(),
            'menu items' => MenuItem::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
            'tables' => Table::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
            'orders' => Order::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
            'payments' => Payment::query()->where('restaurant_id', $restaurant->id)->count(),
            'feedback' => Feedback::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
            'tips' => Tip::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
        ];

        $summary = collect($counts)
            ->map(fn (int $count, string $label): string => "{$count} {$label}")
            ->implode(', ');

        $this->command?->info("Trial visual demo data is ready: {$summary}.");
    }

    /**
     * @return array<int, User>
     */
    private function ensureWaiters(Restaurant $restaurant): array
    {
        $definitions = [
            ['name' => 'Amina Msuya', 'email' => 'amina.waiter@trial.tiptap.test', 'phone' => '255712410101', 'online' => true],
            ['name' => 'Juma Kweka', 'email' => 'juma.waiter@trial.tiptap.test', 'phone' => '255713410102', 'online' => true],
            ['name' => 'Neema Mhando', 'email' => 'neema.waiter@trial.tiptap.test', 'phone' => '255714410103', 'online' => true],
            ['name' => 'Kelvin Mwakalinga', 'email' => 'kelvin.waiter@trial.tiptap.test', 'phone' => '255715410104', 'online' => false],
            ['name' => 'Zawadi Salum', 'email' => 'zawadi.waiter@trial.tiptap.test', 'phone' => '255716410105', 'online' => false],
        ];

        $waiters = [];

        foreach ($definitions as $index => $definition) {
            $waiter = User::query()->firstOrNew(['email' => $definition['email']]);

            if (! $waiter->exists) {
                $waiter->password = Hash::make(Str::random(48));
                $waiter->auth_provider = 'email';
                $waiter->email_verified_at = now();
                $waiter->global_waiter_number = User::generateGlobalWaiterNumber();
            }

            $waiter->forceFill([
                'name' => $definition['name'],
                'restaurant_id' => $restaurant->id,
                'phone' => $definition['phone'],
                'location' => 'Dar es Salaam',
                'waiter_code' => 'TRIAL-W'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'employment_type' => 'permanent',
                'linked_until' => null,
                'digital_tips_enabled' => true,
                'is_online' => $definition['online'],
                'last_online_at' => $definition['online'] ? now()->subMinutes(($index + 1) * 3) : now()->subHours($index + 2),
            ])->save();

            $waiter->syncRoles(['waiter']);
            $waiters[] = $waiter;
        }

        return $waiters;
    }

    private function ensureSupervisor(Restaurant $restaurant): User
    {
        $supervisor = User::query()->firstOrNew(['email' => 'rehema.supervisor@trial.tiptap.test']);

        if (! $supervisor->exists) {
            $supervisor->password = Hash::make(Str::random(48));
            $supervisor->auth_provider = 'email';
            $supervisor->email_verified_at = now();
        }

        $supervisor->forceFill([
            'name' => 'Rehema Mwinyi',
            'restaurant_id' => $restaurant->id,
            'phone' => '255717410106',
            'location' => 'Dar es Salaam',
            'employment_type' => 'permanent',
            'linked_until' => null,
            'is_online' => true,
            'last_online_at' => now()->subMinutes(2),
        ])->save();

        $supervisor->syncRoles(['floor_supervisor']);

        return $supervisor;
    }

    /**
     * @return array<int, TableZone>
     */
    private function ensureZones(Restaurant $restaurant, User $supervisor): array
    {
        $definitions = [
            ['name' => 'Main Hall', 'sort_order' => 1],
            ['name' => 'Garden Terrace', 'sort_order' => 2],
            ['name' => 'VIP Lounge', 'sort_order' => 3],
        ];

        $zones = [];

        foreach ($definitions as $definition) {
            $zone = TableZone::withoutGlobalScopes()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $definition['name']],
                ['sort_order' => $definition['sort_order'], 'supervisor_id' => $supervisor->id],
            );
            $zones[] = $zone;
        }

        $supervisor->forceFill(['zone_id' => $zones[0]->id])->save();

        return $zones;
    }

    /**
     * @param  array<int, TableZone>  $zones
     * @param  array<int, User>  $waiters
     * @return array<int, Table>
     */
    private function ensureTables(Restaurant $restaurant, array $zones, array $waiters): array
    {
        $definitions = [
            ['name' => 'Table 01', 'zone' => 0, 'capacity' => 2],
            ['name' => 'Table 02', 'zone' => 0, 'capacity' => 4],
            ['name' => 'Table 03', 'zone' => 0, 'capacity' => 4],
            ['name' => 'Table 04', 'zone' => 0, 'capacity' => 6],
            ['name' => 'Table 05', 'zone' => 0, 'capacity' => 4],
            ['name' => 'Table 06', 'zone' => 0, 'capacity' => 2],
            ['name' => 'Terrace 01', 'zone' => 1, 'capacity' => 4],
            ['name' => 'Terrace 02', 'zone' => 1, 'capacity' => 6],
            ['name' => 'Terrace 03', 'zone' => 1, 'capacity' => 4],
            ['name' => 'VIP 01', 'zone' => 2, 'capacity' => 6],
            ['name' => 'VIP 02', 'zone' => 2, 'capacity' => 8],
            ['name' => 'VIP 03', 'zone' => 2, 'capacity' => 4],
        ];

        $tables = [];

        foreach ($definitions as $index => $definition) {
            $table = Table::withoutGlobalScopes()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $definition['name']],
                [
                    'waiter_id' => $waiters[$index % count($waiters)]->id,
                    'zone_id' => $zones[$definition['zone']]->id,
                    'capacity' => $definition['capacity'],
                    'is_active' => true,
                    'table_tag' => 'TRL'.$restaurant->id.'T'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                ],
            );
            $tables[] = $table;
        }

        return $tables;
    }

    /**
     * @return array<int, MenuItem>
     */
    private function ensureMenu(Restaurant $restaurant): array
    {
        $definitions = [
            'Starters' => [
                ['Beef Sambusa', 'Crispy sambusa served with fresh tomato kachumbari.', 3500, 8],
                ['Chicken Mishkaki', 'Char-grilled chicken skewers with house pili pili.', 9000, 14],
                ['Spiced Cassava', 'Golden cassava bites with coconut chilli dip.', 6500, 12],
                ['Garden Salad', 'Fresh greens, avocado, cucumber and citrus dressing.', 7000, 8],
            ],
            'Main Course' => [
                ['Grilled Tilapia', 'Whole lake tilapia served with chips or ugali.', 28000, 30],
                ['Chicken Pilau', 'Fragrant coastal pilau with tender grilled chicken.', 18000, 24],
                ['Ugali Nyama Choma', 'Charcoal-grilled beef, ugali and seasonal vegetables.', 24000, 28],
                ['Coconut Fish Curry', 'Fish fillet simmered in a rich coconut curry.', 22000, 26],
                ['Chips Mayai Special', 'Classic chips mayai with beef strips and salad.', 14000, 18],
                ['Vegetable Biryani', 'Aromatic rice with roasted vegetables and raita.', 16000, 22],
            ],
            'Drinks' => [
                ['Passion Juice', 'Freshly blended passion fruit juice.', 5000, 5],
                ['Mango Juice', 'Fresh seasonal mango juice.', 5000, 5],
                ['Tropical Mocktail', 'Passion, pineapple, lime and mint.', 8500, 7],
                ['Mineral Water', 'Chilled 500ml bottled water.', 2500, 2],
            ],
            'Desserts' => [
                ['Kaimati', 'Warm cardamom dumplings with date syrup.', 6500, 10],
                ['Coconut Ice Cream', 'Creamy coconut ice cream with toasted cashews.', 7500, 5],
            ],
        ];

        $items = [];

        foreach ($definitions as $categoryIndex => $categoryItems) {
            $categoryName = is_string($categoryIndex) ? $categoryIndex : 'Menu';
            $category = Category::withoutGlobalScopes()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $categoryName],
                ['sort_order' => count($items) + 1],
            );

            foreach ($categoryItems as [$name, $description, $price, $minutes]) {
                $items[] = MenuItem::withoutGlobalScopes()->updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $name],
                    [
                        'category_id' => $category->id,
                        'description' => $description,
                        'price' => $price,
                        'preparation_time' => $minutes,
                        'is_available' => true,
                    ],
                );
            }
        }

        return $items;
    }

    /**
     * @param  array<int, User>  $waiters
     */
    private function ensureAssignments(Restaurant $restaurant, array $waiters): void
    {
        foreach ($waiters as $waiter) {
            $assignment = WaiterRestaurantAssignment::query()
                ->where('user_id', $waiter->id)
                ->where('restaurant_id', $restaurant->id)
                ->whereNull('unlinked_at')
                ->first() ?? new WaiterRestaurantAssignment;

            $assignment->forceFill([
                'user_id' => $waiter->id,
                'restaurant_id' => $restaurant->id,
                'linked_at' => now()->subMonths(5),
                'unlinked_at' => null,
                'employment_type' => 'permanent',
                'linked_until' => null,
            ])->save();
        }
    }

    /**
     * @param  array<int, User>  $waiters
     */
    private function ensureRoster(Restaurant $restaurant, User $manager, array $waiters): void
    {
        for ($dayOffset = -2; $dayOffset <= 6; $dayOffset++) {
            foreach ($waiters as $index => $waiter) {
                $date = now()->addDays($dayOffset)->toDateString();
                $isEvenShift = ($index + $dayOffset) % 2 === 0;

                WaiterShift::query()->updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'user_id' => $waiter->id,
                        'shift_date' => $date,
                    ],
                    [
                        'starts_at' => $isEvenShift ? '08:00:00' : '14:00:00',
                        'ends_at' => $isEvenShift ? '16:00:00' : '22:00:00',
                        'label' => $isEvenShift ? 'Morning Shift' : 'Evening Shift',
                        'notes' => self::MARKER,
                        'created_by' => $manager->id,
                    ],
                );
            }
        }
    }

    /**
     * @param  array<int, User>  $waiters
     */
    private function ensurePayroll(Restaurant $restaurant, User $manager, array $waiters): void
    {
        foreach ([1, 2, 3] as $monthOffset) {
            $period = now()->subMonths($monthOffset)->format('Y-m');

            foreach ($waiters as $index => $waiter) {
                $basic = 420000 + ($index * 25000);
                $allowances = 60000 + ($index * 5000);
                $paye = 25000 + ($index * 2500);
                $nssf = (int) round($basic * 0.10);

                WaiterSalaryPayment::query()->updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'user_id' => $waiter->id,
                        'period_month' => $period,
                    ],
                    [
                        'basic_salary' => $basic,
                        'allowances' => $allowances,
                        'paye' => $paye,
                        'nssf' => $nssf,
                        'net_pay' => $basic + $allowances - $paye - $nssf,
                        'paid_at' => now()->subMonths($monthOffset)->endOfMonth()->subDays(2),
                        'confirmed_by' => $manager->id,
                    ],
                );
            }
        }
    }

    /**
     * @param  array<int, User>  $waiters
     * @param  array<int, Table>  $tables
     * @param  array<int, MenuItem>  $menuItems
     * @return array<int, array{order: Order, waiter: User, status: string}>
     */
    private function ensureOrders(
        Restaurant $restaurant,
        array $waiters,
        array $tables,
        array $menuItems,
    ): array {
        $customers = [
            'Asha Mrema', 'John Mushi', 'Neema Peter', 'David Massawe', 'Fatma Ali',
            'Grace Mollel', 'Hassan Said', 'Mary Joseph', 'Baraka Lema', 'Sophia Kimaro',
        ];
        $todayStatuses = [
            OrderWorkflow::RECEIVED,
            OrderWorkflow::ACCEPTED,
            OrderWorkflow::PREPARING,
            OrderWorkflow::PREPARING,
            OrderWorkflow::READY,
            OrderWorkflow::READY,
            OrderWorkflow::SERVED,
            OrderWorkflow::SERVED,
            OrderWorkflow::COMPLETED,
            OrderWorkflow::COMPLETED,
        ];
        $orders = [];
        $orderCount = 52;

        for ($index = 0; $index < $orderCount; $index++) {
            $dayOffset = $index < count($todayStatuses) ? 0 : 1 + (($index - count($todayStatuses)) % 21);
            $status = $dayOffset === 0 ? $todayStatuses[$index] : OrderWorkflow::COMPLETED;
            $placedAt = now()
                ->subDays($dayOffset)
                ->startOfDay()
                ->addHours(10 + (($index * 3) % 11))
                ->addMinutes(($index * 13) % 55);
            if ($dayOffset === 0) {
                $placedAt = now()->subMinutes(90 + ($index * 18));
            }

            $waiter = $waiters[$index % count($waiters)];
            $table = $tables[$index % count($tables)];
            $lineItems = [
                ['item' => $menuItems[$index % count($menuItems)], 'quantity' => 1 + ($index % 2)],
                ['item' => $menuItems[($index + 5) % count($menuItems)], 'quantity' => 1],
            ];
            if ($index % 3 === 0) {
                $lineItems[] = ['item' => $menuItems[($index + 10) % count($menuItems)], 'quantity' => 1];
            }

            $total = collect($lineItems)->sum(
                fn (array $line): float => (float) $line['item']->price * $line['quantity'],
            );
            $marker = self::MARKER.':order:'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $timestamps = $this->workflowTimestamps($status, $placedAt);

            $order = Order::withoutGlobalScopes()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'notes' => $marker],
                array_merge([
                    'waiter_id' => $waiter->id,
                    'table_number' => $table->name,
                    'customer_name' => $customers[$index % count($customers)],
                    'customer_phone' => '2557'.str_pad((string) (240100000 + $index), 8, '0', STR_PAD_LEFT),
                    'whatsapp_jid' => '2557'.str_pad((string) (240100000 + $index), 8, '0', STR_PAD_LEFT).'@s.whatsapp.net',
                    'status' => $status,
                    'total_amount' => $total,
                    'is_vip' => str_starts_with($table->name, 'VIP'),
                ], $timestamps),
            );
            $order->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt->copy()->addMinutes(45)])->saveQuietly();

            OrderItem::query()->where('order_id', $order->id)->delete();
            foreach ($lineItems as $line) {
                $item = $line['item'];
                $quantity = $line['quantity'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $quantity,
                    'price' => $item->price,
                    'total' => (float) $item->price * $quantity,
                    'status' => in_array($status, [OrderWorkflow::READY, OrderWorkflow::SERVED, OrderWorkflow::COMPLETED], true)
                        ? 'ready'
                        : ($status === OrderWorkflow::PREPARING ? 'cooking' : 'pending'),
                ]);
            }

            if ($status === OrderWorkflow::COMPLETED) {
                $payment = Payment::query()->updateOrCreate(
                    ['transaction_reference' => 'TRIAL-DEMO-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                    [
                        'order_id' => $order->id,
                        'restaurant_id' => $restaurant->id,
                        'waiter_id' => $waiter->id,
                        'customer_phone' => $order->customer_phone,
                        'amount' => $total,
                        'method' => ['cash', 'mobile', 'card'][$index % 3],
                        'payment_type' => 'order',
                        'status' => 'completed',
                        'description' => self::MARKER,
                    ],
                );
                $paidAt = $placedAt->copy()->addMinutes(48);
                $payment->forceFill(['created_at' => $paidAt, 'updated_at' => $paidAt])->saveQuietly();
            }

            $orders[] = ['order' => $order, 'waiter' => $waiter, 'status' => $status];
        }

        return $orders;
    }

    /**
     * @return array<string, Carbon|null>
     */
    private function workflowTimestamps(string $status, Carbon $placedAt): array
    {
        $stageIndex = array_search($status, OrderWorkflow::PIPELINE, true);
        $values = [
            'received_at' => $placedAt,
            'accepted_at' => null,
            'preparing_at' => null,
            'ready_at' => null,
            'served_at' => null,
            'completed_at' => null,
        ];

        if ($stageIndex === false) {
            return $values;
        }

        $columns = ['received_at', 'accepted_at', 'preparing_at', 'ready_at', 'served_at', 'completed_at'];
        $increments = [0, 2, 7, 28, 35, 48];

        foreach ($columns as $index => $column) {
            if ($index <= $stageIndex) {
                $values[$column] = $placedAt->copy()->addMinutes($increments[$index]);
            }
        }

        return $values;
    }

    /**
     * @param  array<int, User>  $waiters
     * @param  array<int, Table>  $tables
     */
    private function ensureCustomerRequests(Restaurant $restaurant, array $waiters, array $tables): void
    {
        $definitions = [
            ['table' => 1, 'waiter' => 1, 'type' => 'call_waiter', 'status' => 'pending'],
            ['table' => 4, 'waiter' => 4, 'type' => 'request_bill', 'status' => 'pending'],
            ['table' => 7, 'waiter' => 2, 'type' => 'call_waiter', 'status' => 'completed'],
            ['table' => 9, 'waiter' => 4, 'type' => 'request_bill', 'status' => 'completed'],
        ];

        foreach ($definitions as $definition) {
            $table = $tables[$definition['table']];
            CustomerRequest::withoutGlobalScopes()->updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'table_id' => $table->id,
                    'type' => $definition['type'],
                    'status' => $definition['status'],
                ],
                [
                    'table_number' => $table->name,
                    'waiter_id' => $waiters[$definition['waiter']]->id,
                ],
            );
        }
    }

    /**
     * @param  array<int, Table>  $tables
     */
    private function ensureMenuEngagement(Restaurant $restaurant, array $tables): void
    {
        $statuses = [
            MenuEngagementSession::STATUS_PENDING,
            MenuEngagementSession::STATUS_NOTIFIED,
            MenuEngagementSession::STATUS_CONVERTED,
            MenuEngagementSession::STATUS_DISMISSED,
            MenuEngagementSession::STATUS_PENDING,
            MenuEngagementSession::STATUS_CONVERTED,
        ];

        foreach ($statuses as $index => $status) {
            $viewedAt = now()->subMinutes(4 + ($index * 9));
            $table = $tables[($index * 2) % count($tables)];

            MenuEngagementSession::query()->updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'wa_id' => '25571099'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                ],
                [
                    'table_id' => $table->id,
                    'table_number' => $table->name,
                    'menu_viewed_at' => $viewedAt,
                    'converted_at' => $status === MenuEngagementSession::STATUS_CONVERTED ? $viewedAt->copy()->addMinutes(5) : null,
                    'notified_at' => $status === MenuEngagementSession::STATUS_NOTIFIED ? $viewedAt->copy()->addMinutes(10) : null,
                    'dismissed_at' => $status === MenuEngagementSession::STATUS_DISMISSED ? $viewedAt->copy()->addMinutes(12) : null,
                    'status' => $status,
                ],
            );
        }
    }

    /**
     * @param  array<int, array{order: Order, waiter: User, status: string}>  $orders
     */
    private function ensureFeedbackAndTips(Restaurant $restaurant, array $orders): void
    {
        $comments = [
            'Huduma ilikuwa ya haraka na yenye ukarimu.',
            'Chakula kilikuwa kitamu sana na kilifika kikiwa cha moto.',
            'Great atmosphere, clean tables and attentive staff.',
            'The grilled tilapia was excellent. I will order it again.',
            'Very friendly waiter and a smooth payment experience.',
            'Pilau ilikuwa nzuri; ningependa portion iwe kubwa kidogo.',
        ];
        $feedbackTypes = [
            FeedbackType::Waiter,
            FeedbackType::Food,
            FeedbackType::Restaurant,
        ];

        foreach ($orders as $index => $entry) {
            if ($entry['status'] !== OrderWorkflow::COMPLETED) {
                continue;
            }

            $order = $entry['order'];
            $waiter = $entry['waiter'];

            if ($index % 2 === 0) {
                $type = $feedbackTypes[$index % count($feedbackTypes)];
                $feedback = Feedback::withoutGlobalScopes()->updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'order_id' => $order->id, 'type' => $type->value],
                    [
                        'waiter_id' => $waiter->id,
                        'rating' => 3 + ($index % 3),
                        'comment' => $comments[$index % count($comments)],
                    ],
                );
                $feedback->forceFill(['created_at' => $order->updated_at, 'updated_at' => $order->updated_at])->saveQuietly();
            }

            if ($index % 3 === 0) {
                $payment = Payment::query()->where('order_id', $order->id)->first();
                $tip = Tip::withoutGlobalScopes()->updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'order_id' => $order->id, 'waiter_id' => $waiter->id],
                    [
                        'payment_id' => $payment?->id,
                        'amount' => [2000, 3000, 5000, 7500][$index % 4],
                    ],
                );
                $tip->forceFill(['created_at' => $order->updated_at, 'updated_at' => $order->updated_at])->saveQuietly();
            }
        }
    }
}
