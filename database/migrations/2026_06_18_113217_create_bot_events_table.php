<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_events', function (Blueprint $table): void {
            $table->id();
            $table->string('wa_id', 32)->nullable()->comment('WhatsApp phone digits');
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 40);
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['restaurant_id', 'occurred_at']);
            $table->index('wa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_events');
    }
};
