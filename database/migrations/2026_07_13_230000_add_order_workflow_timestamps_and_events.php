<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('received_at');
            $table->timestamp('preparing_at')->nullable()->after('accepted_at');
            $table->timestamp('ready_at')->nullable()->after('preparing_at');
            $table->timestamp('served_at')->nullable()->after('ready_at');
            $table->timestamp('completed_at')->nullable()->after('served_at');
        });

        Schema::create('order_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'to_status', 'created_at']);
            $table->index(['order_id', 'created_at']);
        });

        // Normalize legacy statuses and backfill timestamps.
        DB::table('orders')->orderBy('id')->chunkById(200, function ($orders): void {
            foreach ($orders as $order) {
                $raw = strtolower((string) $order->status);
                $canonical = match ($raw) {
                    'pending' => 'received',
                    'confirmed' => 'accepted',
                    'paid' => 'completed',
                    default => $raw,
                };

                $updates = [];
                if ($canonical !== $raw) {
                    $updates['status'] = $canonical;
                }

                $created = $order->created_at;
                $updated = $order->updated_at ?? $created;

                if (empty($order->received_at) && in_array($canonical, ['received', 'accepted', 'preparing', 'ready', 'served', 'completed'], true)) {
                    $updates['received_at'] = $created;
                }

                if (empty($order->accepted_at) && in_array($canonical, ['accepted', 'preparing', 'ready', 'served', 'completed'], true)) {
                    $updates['accepted_at'] = $updated;
                }

                if (empty($order->preparing_at) && in_array($canonical, ['preparing', 'ready', 'served', 'completed'], true)) {
                    $updates['preparing_at'] = $updated;
                }

                if (empty($order->ready_at) && in_array($canonical, ['ready', 'served', 'completed'], true)) {
                    $updates['ready_at'] = $updated;
                }

                if (empty($order->served_at) && in_array($canonical, ['served', 'completed'], true)) {
                    $updates['served_at'] = $updated;
                }

                if (empty($order->completed_at) && $canonical === 'completed') {
                    $updates['completed_at'] = $updated;
                }

                if ($updates !== []) {
                    DB::table('orders')->where('id', $order->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_events');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'received_at',
                'accepted_at',
                'preparing_at',
                'ready_at',
                'served_at',
                'completed_at',
            ]);
        });
    }
};
