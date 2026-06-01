<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\WaiterSalaryPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $query = WaiterSalaryPayment::query()
            ->with(['restaurant', 'user', 'confirmedByUser'])
            ->latest('paid_at');

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->integer('restaurant_id'));
        }
        if ($request->filled('period_month')) {
            $query->where('period_month', $request->string('period_month')->toString());
        }

        $totalNet = (float) (clone $query)->sum('net_pay');
        $payments = $query->paginate(25)->withQueryString();
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        $months = WaiterSalaryPayment::query()
            ->select('period_month')
            ->distinct()
            ->orderByDesc('period_month')
            ->limit(24)
            ->pluck('period_month');

        return view('admin.payroll.index', compact('payments', 'restaurants', 'totalNet', 'months'));
    }
}
