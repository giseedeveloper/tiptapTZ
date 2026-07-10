<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->foreignId('branch_group_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('restaurant_branch_groups')
                  ->nullOnDelete();
            $table->string('branch_name')->nullable()->after('branch_group_id');
            $table->integer('branch_sort_order')->default(0)->after('branch_name');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['branch_group_id']);
            $table->dropColumn(['branch_group_id', 'branch_name', 'branch_sort_order']);
        });
    }
};
