<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesRestaurantAccess;
use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipController extends Controller
{
    use AuthorizesRestaurantAccess;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'waiter_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('restaurant_id', $request->input('restaurant_id'))
                ),
            ],
            'order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->where(
                    fn ($query) => $query->where('restaurant_id', $request->input('restaurant_id'))
                ),
            ],
            'amount' => 'required|numeric|min:1',
        ]);

        $this->authorizeRestaurantAccess($request, (int) $validated['restaurant_id']);

        $tip = Tip::withoutGlobalScopes()->create($validated);

        return response()->json($tip, 201);
    }
}
