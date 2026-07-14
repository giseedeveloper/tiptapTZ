<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurants', 'tip_settings')) {
                $table->json('tip_settings')->nullable()->after('kitchen_token_generated_at');
            }
            if (! Schema::hasColumn('restaurants', 'busy_mode')) {
                $table->boolean('busy_mode')->default(false)->after('tip_settings');
            }
            if (! Schema::hasColumn('restaurants', 'busy_eta_multiplier')) {
                $table->decimal('busy_eta_multiplier', 3, 1)->default(1.5)->after('busy_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            foreach (['tip_settings', 'busy_mode', 'busy_eta_multiplier'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
