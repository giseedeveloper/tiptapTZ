<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreWithdrawalRequest;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Withdrawal;
use App\Services\RestaurantWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private readonly RestaurantWalletService $wallet,
    ) {}

    public function index(): View
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        return view('manager.wallet.index', [
            'restaurant' => $restaurant,
            'summary' => $this->wallet->summary($restaurant),
            'recentPayments' => Payment::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['paid', 'completed'])
                ->latest()
                ->limit(15)
                ->get(),
            'withdrawals' => Withdrawal::query()
                ->where('restaurant_id', $restaurant->id)
                ->latest()
                ->limit(15)
                ->get(),
            'minWithdrawal' => (float) Setting::get('min_withdrawal', 0),
        ]);
    }

    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        $restaurant = Auth::user()->restaurant;

        abort_unless($restaurant, 403);

        $amount = round((float) $request->validated('amount'), 2);
        $available = $this->wallet->availableBalance($restaurant);

        if ($amount > $available) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Amount exceeds available wallet balance ('.number_format($available, 0).').']);
        }

        Withdrawal::query()->create([
            'restaurant_id' => $restaurant->id,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $request->validated('payment_method'),
            'payment_details' => $request->validated('payment_details'),
        ]);

        return back()->with('success', 'Withdrawal request submitted. Admin will review and process it.');
    }
}
