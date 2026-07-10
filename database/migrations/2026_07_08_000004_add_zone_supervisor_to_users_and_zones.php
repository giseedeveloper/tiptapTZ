<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_zones', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('zone_id')
                  ->nullable()
                  ->after('restaurant_id')
                  ->constrained('table_zones')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('table_zones', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }
};
