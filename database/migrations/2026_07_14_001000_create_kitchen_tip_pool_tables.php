<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->default('kitchen'); // kitchen | house | custom
            $table->boolean('is_enabled')->default(false);
            $table->string('distribution_method')->default('equal'); // equal | weighted
            $table->timestamps();

            $table->unique(['restaurant_id', 'code']);
        });

        Schema::create('tip_pool_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_pool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('weight')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tip_pool_id', 'user_id']);
        });

        Schema::create('tip_pool_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_pool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('distribution_method');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tip_pool_id', 'created_at']);
        });

        Schema::create('tip_pool_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_pool_contribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tip_id')->nullable()->constrained('tips')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('weight_used')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tip_pool_id')->nullable()->after('waiter_id')->constrained('tip_pools')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tip_pool_id');
        });

        Schema::dropIfExists('tip_pool_allocations');
        Schema::dropIfExists('tip_pool_contributions');
        Schema::dropIfExists('tip_pool_members');
        Schema::dropIfExists('tip_pools');
    }
};
