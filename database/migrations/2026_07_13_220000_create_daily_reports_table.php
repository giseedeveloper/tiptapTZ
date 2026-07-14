<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->json('metrics');
            $table->string('pdf_path')->nullable();
            $table->string('excel_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('generation_source')->default('manual'); // scheduled|manual|api
            $table->timestamps();

            $table->unique(['restaurant_id', 'report_date']);
            $table->index(['report_date', 'restaurant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
