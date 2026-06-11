<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;

class FoodRatingController extends Controller
{
    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $feedbacks = Feedback::query()
            ->forFood()
            ->with(['order.items'])
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->paginate(10);

        $avgRating = Feedback::query()
            ->forFood()
            ->where('restaurant_id', $restaurantId)
            ->avg('rating') ?? 0;

        $totalReviews = Feedback::query()
            ->forFood()
            ->where('restaurant_id', $restaurantId)
            ->count();

        $ratingBreakdown = Feedback::query()
            ->forFood()
            ->where('restaurant_id', $restaurantId)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->all();

        return view('manager.food-ratings.index', compact(
            'feedbacks',
            'avgRating',
            'totalReviews',
            'ratingBreakdown',
        ));
    }
}
