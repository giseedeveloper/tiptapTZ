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
            $table->string('auth_provider', 20)->default('email')->after('email');
            $table->string('auth_provider_id')->nullable()->after('auth_provider');
            $table->index(['auth_provider', 'auth_provider_id']);
        });

        DB::table('users')->whereNull('auth_provider')->update(['auth_provider' => 'email']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['auth_provider', 'auth_provider_id']);
            $table->dropColumn(['auth_provider', 'auth_provider_id']);
        });
    }
};
