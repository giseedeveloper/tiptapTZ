<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('wa_id', 32)->unique()->comment('WhatsApp phone (digits only) used as session key');
            $table->string('state', 64)->default('START');
            $table->string('lang', 2)->default('en');
            $table->json('data')->nullable()->comment('Full session payload (cart, restaurant_id, table_id, etc.)');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('state');
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_sessions');
    }
};
