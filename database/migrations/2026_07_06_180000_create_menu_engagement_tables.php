<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurants', 'menu_engagement_alerts_enabled')) {
                $table->boolean('menu_engagement_alerts_enabled')->default(true)->after('menu_pdf');
            }
            if (! Schema::hasColumn('restaurants', 'menu_engagement_timeout_minutes')) {
                $table->unsignedTinyInteger('menu_engagement_timeout_minutes')->default(10)->after('menu_engagement_alerts_enabled');
            }
        });

        Schema::create('menu_engagement_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('table_number')->nullable();
            $table->string('wa_id', 32)->nullable();
            $table->timestamp('menu_viewed_at');
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['restaurant_id', 'status', 'menu_viewed_at'], 'mes_rest_status_viewed_idx');
            $table->index(['wa_id', 'restaurant_id', 'status'], 'mes_wa_rest_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_engagement_sessions');

        Schema::table('restaurants', function (Blueprint $table): void {
            if (Schema::hasColumn('restaurants', 'menu_engagement_timeout_minutes')) {
                $table->dropColumn('menu_engagement_timeout_minutes');
            }
            if (Schema::hasColumn('restaurants', 'menu_engagement_alerts_enabled')) {
                $table->dropColumn('menu_engagement_alerts_enabled');
            }
        });
    }
};
