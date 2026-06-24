<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_leads', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('market', 8)->default('tz');
            $table->string('source', 64)->default('efficiency_guide');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['email', 'market']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_leads');
    }
};
