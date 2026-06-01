<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $restaurants = Restaurant::query()
            ->withCount(['menuItems' => fn ($q) => $q->withoutGlobalScopes()])
            ->orderBy('name')
            ->get();

        return view('admin.menus.index', compact('restaurants'));
    }

    public function show(string $restaurant): View
    {
        $restaurant = Restaurant::query()->findOrFail($restaurant);

        $menuItems = MenuItem::withoutGlobalScope(RestaurantScope::class)
            ->with('category')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('admin.menus.show', compact('restaurant', 'menuItems'));
    }
}
