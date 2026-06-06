<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreWithdrawalRequest;
use App\Http\Requests\Manager\UpdatePayoutProfileRequest;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalStatusNotification;
use App\Services\RestaurantWalletService;
use App\Services\WalletStatementExporter;
use App\Services\WithdrawalSubmissionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletController extends Controller
{
    public function __construct(
        private readonly RestaurantWalletService $wallet,
        private readonly WithdrawalSubmissionService $withdrawals,
        private readonly WalletStatementExporter $exporter,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        abort_unless($restaurant, 403);

        $withdrawalAlerts = $user->unreadNotifications()
            ->where('type', WithdrawalStatusNotification::class)
            ->latest()
            ->take(5)
            ->get();

        return view('manager.wallet.index', [
            'restaurant' => $restaurant,
            'summary' => $this->wallet->summary($restaurant),
            'breakdown' => $this->wallet->paymentBreakdown($restaurant),
            'recentPayments' => Payment::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['paid', 'completed'])
                ->latest()
                ->paginate(15, ['*'], 'payments_page')
                ->withQueryString(),
            'withdrawals' => Withdrawal::query()
                ->where('restaurant_id', $restaurant->id)
                ->latest()
                ->paginate(10, ['*'], 'withdrawals_page')
                ->withQueryString(),
            'minWithdrawal' => (float) Setting::get('min_withdrawal', 0),
            'withdrawalAlerts' => $withdrawalAlerts,
            'useSavedPayoutDefault' => $restaurant->hasPayoutProfile() && ! old('payment_method'),
        ]);
    }

    public function updatePayoutProfile(UpdatePayoutProfileRequest $request): RedirectResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        $restaurant->update($request->validated());

        return back()->with('success', 'Payout profile saved. You can use it for future withdrawal requests.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        Auth::user()
            ->unreadNotifications()
            ->where('type', WithdrawalStatusNotification::class)
            ->update(['read_at' => now()]);

        return back();
    }

    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        $amount = round((float) $request->validated('amount'), 2);
        $payout = $request->payoutDetails();

        try {
            $this->withdrawals->submit(
                $restaurant,
                $amount,
                $payout['payment_method'],
                $payout['payment_details'],
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return back()->with('success', 'Withdrawal request submitted. Admin will review and process it.');
    }

    public function export(Request $request): StreamedResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        $startDate = $request->string('start_date')->trim();
        $endDate = $request->string('end_date')->trim();

        $start = $startDate->isNotEmpty() ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate->isNotEmpty() ? Carbon::parse($endDate)->endOfDay() : null;

        return $this->exporter->stream($restaurant, $start, $end);
    }
}
