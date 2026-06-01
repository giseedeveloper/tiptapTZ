<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $restaurants = collect();
        $users = collect();
        $orders = collect();
        $waiters = collect();

        if (strlen($query) >= 2) {
            $like = '%'.$query.'%';

            $restaurants = Restaurant::query()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                })
                ->limit(10)
                ->get();

            $users = User::query()
                ->with('restaurant')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->limit(10)
                ->get();

            $waiters = User::role('waiter')
                ->with('restaurant')
                ->where(function ($q) use ($like, $query) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('global_waiter_number', 'like', $like);
                    if (is_numeric($query)) {
                        $q->orWhere('id', (int) $query);
                    }
                })
                ->limit(10)
                ->get();

            $orders = Order::query()
                ->with('restaurant')
                ->where(function ($q) use ($like, $query) {
                    $q->where('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like)
                        ->orWhere('payment_reference', 'like', $like);
                    if (is_numeric($query)) {
                        $q->orWhere('id', (int) $query);
                    }
                })
                ->latest()
                ->limit(10)
                ->get();

            $paymentOrderIds = Payment::query()
                ->where('transaction_reference', 'like', $like)
                ->pluck('order_id')
                ->filter();

            if ($paymentOrderIds->isNotEmpty()) {
                $paymentOrders = Order::query()
                    ->with('restaurant')
                    ->whereIn('id', $paymentOrderIds)
                    ->latest()
                    ->limit(10)
                    ->get();

                $orders = $orders->merge($paymentOrders)->unique('id')->take(10)->values();
            }
        }

        return view('admin.search.index', compact(
            'query',
            'restaurants',
            'users',
            'orders',
            'waiters',
        ));
    }
}
