<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::withoutGlobalScope(RestaurantScope::class)
            ->with(['restaurant', 'order', 'waiter'])
            ->latest();

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->integer('restaurant_id'));
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->integer('rating'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from')->toString());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to')->toString());
        }

        $feedback = $query->paginate(25)->withQueryString();
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        $avgRating = (float) Feedback::withoutGlobalScope(RestaurantScope::class)
            ->when($request->filled('restaurant_id'), fn ($q) => $q->where('restaurant_id', $request->integer('restaurant_id')))
            ->avg('rating');

        return view('admin.feedback.index', compact('feedback', 'restaurants', 'avgRating'));
    }
}
