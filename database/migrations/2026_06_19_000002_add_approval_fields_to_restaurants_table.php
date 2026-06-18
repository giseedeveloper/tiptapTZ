<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'approval_status')) {
                $table->string('approval_status', 20)->default('pending')->index()->after('is_active');
            }
            if (! Schema::hasColumn('restaurants', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_status');
            }
            if (! Schema::hasColumn('restaurants', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('restaurants', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('restaurants', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('restaurants', 'subscription_package_id')) {
                $table->foreignId('subscription_package_id')->nullable()->after('rejected_at')->constrained('subscription_packages')->nullOnDelete();
            }
            if (! Schema::hasColumn('restaurants', 'plan_selected_at')) {
                $table->timestamp('plan_selected_at')->nullable()->after('subscription_package_id');
            }
            if (! Schema::hasColumn('restaurants', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('plan_selected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'subscription_package_id')) {
                $table->dropConstrainedForeignId('subscription_package_id');
            }
            if (Schema::hasColumn('restaurants', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            $table->dropColumn([
                'approval_status', 'approved_at', 'rejection_reason',
                'rejected_at', 'plan_selected_at', 'trial_ends_at',
            ]);
        });
    }
};
