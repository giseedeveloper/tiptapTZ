<?php

namespace Database\Seeders;

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Enums\BotQrEntryType;
use App\Enums\FeedbackType;
use App\Models\BotEvent;
use App\Models\BotSession;
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
use Illuminate\Support\Facades\Hash;

class TiptapAnalysisSampleSeeder extends Seeder
{
    public const MARKER = 'tiptap_analysis_sample';

    public function run(): void
    {
        if (BotEvent::query()->where('metadata->seed', self::MARKER)->exists()) {
            $this->command?->info('TipTap analysis sample data already exists. Skipping.');

            return;
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $venues = $this->ensureVenues();

        foreach ($venues as $venue) {
            $this->seedBotActivity($venue);
            $this->seedCommerce($venue);
        }

        $this->command?->info('TipTap analysis sample data seeded for '.count($venues).' restaurants (last 30 days).');
        $this->command?->info('Open /admin/tiptap-analysis (TipTap Analytics) and explore each section.');
    }

    /**
     * @return list<array{restaurant: Restaurant, waiter: User}>
     */
    private function ensureVenues(): array
    {
        $definitions = [
            ['name' => 'TIPTAP Demo Grill', 'location' => 'Cape Town', 'phone' => '0820000001', 'qr_bias' => 'table'],
            ['name' => 'Sunset Bistro', 'location' => 'Johannesburg', 'phone' => '0820000002', 'qr_bias' => 'waiter'],
            ['name' => 'Harbour Kitchen', 'location' => 'Durban', 'phone' => '0820000003', 'qr_bias' => 'restaurant'],
        ];

        $venues = [];

        foreach ($definitions as $index => $def) {
            $restaurant = Restaurant::query()->firstOrCreate(
                ['name' => $def['name']],
                [
                    'location' => $def['location'],
                    'phone' => $def['phone'],
                    'is_active' => true,
                ],
            );

            $waiter = User::role('waiter')
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if (! $waiter) {
                $waiter = User::factory()->create([
                    'name' => 'Waiter '.($index + 1),
                    'email' => 'waiter'.($index + 1).'@analysis.demo',
                    'password' => Hash::make('password'),
                    'restaurant_id' => $restaurant->id,
                ]);
                $waiter->assignRole('waiter');
            }

            $venues[] = [
                'restaurant' => $restaurant,
                'waiter' => $waiter,
                'qr_bias' => $def['qr_bias'],
            ];
        }

        return $venues;
    }

    /**
     * @param  array{restaurant: Restaurant, waiter: User, qr_bias: string}  $venue
     */
    private function seedBotActivity(array $venue): void
    {
        $restaurant = $venue['restaurant'];
        $qrBias = $venue['qr_bias'];

        $engagementWeights = [
            BotEngagementEvent::ViewMenu->value => 85,
            BotEngagementEvent::CallWaiter->value => 42,
            BotEngagementEvent::PayBill->value => 38,
            BotEngagementEvent::RateService->value => 28,
            BotEngagementEvent::GiveTips->value => 18,
            BotEngagementEvent::ChangeLanguage->value => 12,
            BotEngagementEvent::ExitBot->value => 22,
        ];

        $funnelCounts = [
            BotFunnelStep::BotHome->value => 94,
            BotFunnelStep::ViewMenu->value => 72,
            BotFunnelStep::AddToCart->value => 48,
            BotFunnelStep::ConfirmOrder->value => 40,
            BotFunnelStep::PayBill->value => 32,
            BotFunnelStep::PaymentSuccess->value => 26,
        ];

        $qrWeights = match ($qrBias) {
            'waiter' => [BotQrEntryType::Waiter->value => 55, BotQrEntryType::Table->value => 30, BotQrEntryType::Restaurant->value => 15],
            'restaurant' => [BotQrEntryType::Waiter->value => 15, BotQrEntryType::Table->value => 25, BotQrEntryType::Restaurant->value => 60],
            default => [BotQrEntryType::Waiter->value => 25, BotQrEntryType::Table->value => 55, BotQrEntryType::Restaurant->value => 20],
        };

        $waCounter = 0;

        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $day = now()->subDays($dayOffset)->startOfDay();
            $dailyFactor = 0.6 + (sin($dayOffset / 3) * 0.2) + (random_int(0, 40) / 100);

            foreach ($engagementWeights as $eventType => $baseCount) {
                $eventsToday = max(0, (int) round(($baseCount / 30) * $dailyFactor));

                for ($i = 0; $i < $eventsToday; $i++) {
                    $waId = $this->waId($restaurant->id, $waCounter++);
                    $at = $day->copy()->addHours(random_int(9, 22))->addMinutes(random_int(0, 59));

                    $this->insertBotEvent($waId, $restaurant->id, $eventType, $at, [
                        'menu_type' => random_int(0, 1) ? 'image' : 'list',
                    ]);
                }
            }

            foreach ($qrWeights as $qrType => $weight) {
                $scans = max(0, (int) round(($weight / 30) * $dailyFactor * 1.2));

                for ($i = 0; $i < $scans; $i++) {
                    $waId = $this->waId($restaurant->id, $waCounter++);
                    $at = $day->copy()->addHours(random_int(10, 21))->addMinutes(random_int(0, 59));
                    $this->insertBotEvent($waId, $restaurant->id, $qrType, $at);
                }
            }

            foreach ($funnelCounts as $step => $baseCount) {
                $count = max(0, (int) round(($baseCount / 30) * $dailyFactor));

                for ($i = 0; $i < $count; $i++) {
                    $waId = $this->waId($restaurant->id, $waCounter++);
                    $at = $day->copy()->addHours(random_int(10, 22))->addMinutes(random_int(0, 59));
                    $this->insertBotEvent($waId, $restaurant->id, $step, $at);
                }
            }
        }

        $langs = ['en', 'en', 'en', 'sw', 'sw'];
        for ($s = 0; $s < 35; $s++) {
            $waId = $this->waId($restaurant->id, 5000 + $s);
            $lang = $langs[$s % count($langs)];
            $at = now()->subDays(random_int(0, 29))->subHours(random_int(0, 12));

            BotSession::query()->updateOrCreate(
                ['wa_id' => $waId],
                [
                    'state' => 'HOME',
                    'lang' => $lang,
                    'data' => [
                        'restaurant_id' => $restaurant->id,
                        'restaurant_name' => $restaurant->name,
                        'seed' => self::MARKER,
                    ],
                    'last_message_at' => $at,
                ],
            );
        }
    }

    /**
     * @param  array{restaurant: Restaurant, waiter: User, qr_bias: string}  $venue
     */
    private function seedCommerce(array $venue): void
    {
        $restaurant = $venue['restaurant'];
        $waiter = $venue['waiter'];

        $category = Category::withoutGlobalScopes()->firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Analysis Demo Menu'],
            ['sort_order' => 1],
        );

        $menuItem = MenuItem::withoutGlobalScopes()->firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Demo Platter'],
            [
                'category_id' => $category->id,
                'description' => 'Sample item for analysis UI.',
                'price' => 150.00,
                'is_available' => true,
            ],
        );

        $comments = [
            'Great service, food was amazing!',
            'Waiter was very helpful via WhatsApp.',
            'Quick payment — loved the bot menu.',
            'Table QR worked perfectly.',
            'Could be faster at peak hour.',
            'Nice atmosphere, will come again.',
        ];

        $lowComments = [
            'Food took a while today.',
            'Menu image did not load first time.',
        ];

        $orderIndex = 0;

        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $day = now()->subDays($dayOffset)->startOfDay();
            $ordersToday = random_int(1, 4);

            for ($n = 0; $n < $ordersToday; $n++) {
                $placedAt = $day->copy()->addHours(random_int(11, 21))->addMinutes(random_int(0, 55));
                $total = (float) (random_int(8, 25) * 50);
                $status = $orderIndex % 5 === 0 ? 'completed' : 'paid';

                $order = Order::withoutGlobalScopes()->create([
                    'restaurant_id' => $restaurant->id,
                    'waiter_id' => $waiter->id,
                    'table_number' => 'T'.(1 + ($orderIndex % 6)),
                    'customer_name' => 'Guest '.($orderIndex + 1),
                    'customer_phone' => '27'.str_pad((string) (700000000 + $orderIndex), 9, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'total_amount' => $total,
                    'notes' => self::MARKER,
                ]);
                $order->created_at = $placedAt;
                $order->updated_at = $placedAt->copy()->addMinutes(45);
                $order->saveQuietly();

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'quantity' => random_int(1, 3),
                    'price' => $menuItem->price,
                    'total' => $total,
                    'status' => 'served',
                ]);

                $method = ['cash', 'ussd', 'card', 'cash', 'ussd'][$orderIndex % 5];
                $paymentType = $orderIndex % 7 === 0 ? 'quick' : 'order';
                $paidAt = $placedAt->copy()->addMinutes(random_int(20, 90));

                Payment::create([
                    'order_id' => $paymentType === 'order' ? $order->id : null,
                    'restaurant_id' => $restaurant->id,
                    'waiter_id' => $waiter->id,
                    'customer_phone' => $order->customer_phone,
                    'amount' => $total,
                    'method' => $method,
                    'payment_type' => $paymentType,
                    'status' => 'paid',
                    'transaction_reference' => 'ANALYSIS-'.strtoupper(substr(md5((string) $order->id), 0, 10)),
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]);

                if ($orderIndex % 2 === 0) {
                    $rating = $orderIndex % 9 === 0 ? 2 : random_int(3, 5);
                    $commentPool = $rating <= 2 ? $lowComments : $comments;

                    Feedback::withoutGlobalScopes()->create([
                        'restaurant_id' => $restaurant->id,
                        'order_id' => $order->id,
                        'waiter_id' => $waiter->id,
                        'type' => [FeedbackType::Waiter, FeedbackType::Food, FeedbackType::Restaurant][$orderIndex % 3],
                        'rating' => $rating,
                        'comment' => $commentPool[$orderIndex % count($commentPool)],
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);
                }

                if ($orderIndex % 3 === 0) {
                    Tip::withoutGlobalScopes()->create([
                        'restaurant_id' => $restaurant->id,
                        'order_id' => $order->id,
                        'waiter_id' => $waiter->id,
                        'amount' => (int) round($total * 0.08),
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);
                }

                $orderIndex++;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function insertBotEvent(string $waId, int $restaurantId, string $eventType, \Illuminate\Support\Carbon $at, array $metadata = []): void
    {
        BotEvent::query()->create([
            'wa_id' => $waId,
            'restaurant_id' => $restaurantId,
            'event_type' => $eventType,
            'metadata' => array_merge(['seed' => self::MARKER], $metadata),
            'occurred_at' => $at,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    private function waId(int $restaurantId, int $counter): string
    {
        return (string) (27000000000 + ($restaurantId * 100000) + $counter);
    }
}
