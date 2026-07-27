<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_portal_passwords', function (Blueprint $table): void {
            $table->string('lookup_hash', 64)->nullable()->after('password')->index();
        });
    }

    public function down(): void
    {
        Schema::table('order_portal_passwords', function (Blueprint $table): void {
            $table->dropIndex(['lookup_hash']);
            $table->dropColumn('lookup_hash');
        });
    }
};
