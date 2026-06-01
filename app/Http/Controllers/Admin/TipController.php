<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\Tip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tip::withoutGlobalScope(RestaurantScope::class)
            ->with(['restaurant', 'waiter', 'order'])
            ->latest();

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->integer('restaurant_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from')->toString());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to')->toString());
        }

        $totalTips = (float) (clone $query)->sum('amount');
        $tips = $query->paginate(25)->withQueryString();
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.tips.index', compact('tips', 'restaurants', 'totalTips'));
    }
}
