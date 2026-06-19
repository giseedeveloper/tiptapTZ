<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_packages', 'waiter_limit')) {
                $table->unsignedInteger('waiter_limit')->nullable()->after('table_limit');
            }
            if (! Schema::hasColumn('subscription_packages', 'capabilities')) {
                $table->json('capabilities')->nullable()->after('features');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn(['waiter_limit', 'capabilities']);
        });
    }
};
