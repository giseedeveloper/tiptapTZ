<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('idempotency_key')->nullable()->after('transaction_reference');
            $table->boolean('is_demo')->default(false)->after('status');
            $table->timestamp('settled_at')->nullable()->after('is_demo');
        });

        DB::table('payments')
            ->where('transaction_reference', '')
            ->update(['transaction_reference' => null]);

        $duplicates = DB::table('payments')
            ->select('transaction_reference')
            ->whereNotNull('transaction_reference')
            ->groupBy('transaction_reference')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('transaction_reference');

        foreach ($duplicates as $reference) {
            $ids = DB::table('payments')
                ->where('transaction_reference', $reference)
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids->slice(1) as $id) {
                DB::table('payments')
                    ->where('id', $id)
                    ->update([
                        'transaction_reference' => substr((string) $reference, 0, 220).'-DUP-'.$id,
                    ]);
            }
        }

        DB::table('payments')
            ->whereIn('status', ['paid', 'completed'])
            ->whereIn('method', ['ussd', 'card', 'mobile'])
            ->whereNull('settled_at')
            ->update(['settled_at' => DB::raw('COALESCE(updated_at, created_at)')]);

        Schema::table('payments', function (Blueprint $table): void {
            $table->unique('transaction_reference', 'payments_transaction_reference_unique');
            $table->unique('idempotency_key', 'payments_idempotency_key_unique');
            $table->index(
                ['restaurant_id', 'status', 'method', 'is_demo'],
                'payments_wallet_eligibility_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_transaction_reference_unique');
            $table->dropUnique('payments_idempotency_key_unique');
            $table->dropIndex('payments_wallet_eligibility_index');
            $table->dropColumn(['idempotency_key', 'is_demo', 'settled_at']);
        });
    }
};
