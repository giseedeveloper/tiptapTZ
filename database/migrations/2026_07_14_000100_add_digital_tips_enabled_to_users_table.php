<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('digital_tips_enabled')->default(false)->after('is_online');
        });

        // Preserve existing tip behavior: currently linked waiters stay tippable
        // until a manager turns tipping off for specific staff.
        if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
            $waiterRoleId = DB::table('roles')->where('name', 'waiter')->where('guard_name', 'web')->value('id');
            $baristaRoleId = DB::table('roles')->where('name', 'barista')->where('guard_name', 'web')->value('id');
            $roleIds = array_values(array_filter([$waiterRoleId, $baristaRoleId]));

            if ($roleIds !== []) {
                $userIds = DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->whereIn('role_id', $roleIds)
                    ->pluck('model_id');

                if ($userIds->isNotEmpty()) {
                    DB::table('users')
                        ->whereIn('id', $userIds)
                        ->whereNotNull('restaurant_id')
                        ->update(['digital_tips_enabled' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('digital_tips_enabled');
        });
    }
};
