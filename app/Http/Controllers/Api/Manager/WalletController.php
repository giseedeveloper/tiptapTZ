<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreWithdrawalRequest;
use App\Http\Requests\Manager\UpdatePayoutProfileRequest;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Withdrawal;
use App\Services\RestaurantWalletService;
use App\Services\WalletStatementExporter;
use App\Services\WithdrawalSubmissionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletController extends Controller
{
    public function __construct(
        private readonly RestaurantWalletService $wallet,
        private readonly WithdrawalSubmissionService $withdrawals,
        private readonly WalletStatementExporter $exporter,
    ) {}

    public function summary(): JsonResponse
    {
        $restaurant = $this->restaurant();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                ],
                'summary' => $this->wallet->summary($restaurant),
                'breakdown' => $this->wallet->paymentBreakdown($restaurant),
                'payout_profile' => $this->payoutProfilePayload($restaurant),
                'min_withdrawal' => (float) Setting::get('min_withdrawal', 0),
                'currency_symbol' => config('tiptap.currency_symbol'),
            ],
        ]);
    }

    public function breakdown(): JsonResponse
    {
        $restaurant = $this->restaurant();

        return response()->json([
            'success' => true,
            'data' => $this->wallet->paymentBreakdown($restaurant),
        ]);
    }

    public function withdrawals(Request $request): JsonResponse
    {
        $restaurant = $this->restaurant();

        $withdrawals = Withdrawal::query()
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($withdrawals->items())
                    ->map(fn (Withdrawal $withdrawal): array => $this->withdrawalPayload($withdrawal))
                    ->values(),
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                ],
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $restaurant = $this->restaurant();

        $payments = Payment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['paid', 'completed'])
            ->with('order:id,table_number')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($payments->items())
                    ->map(fn (Payment $payment): array => $this->paymentPayload($payment))
                    ->values(),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ],
        ]);
    }

    public function storeWithdrawal(StoreWithdrawalRequest $request): JsonResponse
    {
        $restaurant = $this->restaurant();
        $amount = round((float) $request->validated('amount'), 2);
        $payout = $request->payoutDetails();

        try {
            $withdrawal = $this->withdrawals->submit(
                $restaurant,
                $amount,
                $payout['payment_method'],
                $payout['payment_details'],
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted. Admin will review and process it.',
            'data' => [
                'withdrawal' => $this->withdrawalPayload($withdrawal),
                'summary' => $this->wallet->summary($restaurant->fresh()),
            ],
        ], 201);
    }

    public function updatePayoutProfile(UpdatePayoutProfileRequest $request): JsonResponse
    {
        $restaurant = $this->restaurant();
        $restaurant->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payout profile saved.',
            'data' => $this->payoutProfilePayload($restaurant->fresh()),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $restaurant = $this->restaurant();
        [$start, $end] = $this->resolveDateRange($request);

        return $this->exporter->stream($restaurant, $start, $end);
    }

    private function restaurant(): Restaurant
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        return $restaurant;
    }

    /**
     * @return array{payout_method: ?string, payout_details: ?string, is_complete: bool}
     */
    private function payoutProfilePayload(Restaurant $restaurant): array
    {
        return [
            'payout_method' => $restaurant->payout_method,
            'payout_details' => $restaurant->payout_details,
            'is_complete' => $restaurant->hasPayoutProfile(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function withdrawalPayload(Withdrawal $withdrawal): array
    {
        return [
            'id' => $withdrawal->id,
            'amount' => (float) $withdrawal->amount,
            'status' => $withdrawal->status,
            'payment_method' => $withdrawal->payment_method,
            'payment_details' => $withdrawal->payment_details,
            'admin_note' => $withdrawal->admin_note,
            'processed_at' => $withdrawal->processed_at?->toIso8601String(),
            'created_at' => $withdrawal->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPayload(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'table_number' => $payment->order?->table_number,
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
            'payment_type' => $payment->payment_type ?? 'order',
            'status' => $payment->status,
            'transaction_reference' => $payment->transaction_reference,
            'created_at' => $payment->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        $startDate = $request->string('start_date')->trim();
        $endDate = $request->string('end_date')->trim();

        if ($startDate->isEmpty() || $endDate->isEmpty()) {
            return [null, null];
        }

        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }
}
