<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesRestaurantAccess;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    use AuthorizesRestaurantAccess;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'order_id' => [
                'nullable',
                'integer',
                Rule::exists('orders', 'id')->where(
                    fn ($query) => $query->where('restaurant_id', $request->input('restaurant_id'))
                ),
            ],
            'waiter_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('restaurant_id', $request->input('restaurant_id'))
                ),
            ],
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $this->authorizeRestaurantAccess($request, (int) $validated['restaurant_id']);

        $feedback = Feedback::withoutGlobalScopes()->create($validated);

        return response()->json($feedback, 201);
    }
}
