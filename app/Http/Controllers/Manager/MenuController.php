<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $restaurant = Auth::user()->restaurant;
        $categories = Category::where('restaurant_id', $restaurantId)->get();
        $menuItems = MenuItem::with('category')->where('restaurant_id', $restaurantId)->latest()->get();

        return view('manager.menu.index', compact('categories', 'menuItems', 'restaurant'));
    }

    public function updateBusyMode(Request $request)
    {
        $request->validate([
            'busy_mode' => 'nullable|boolean',
            'busy_eta_multiplier' => 'nullable|numeric|min:1|max:5',
        ]);

        $restaurant = Auth::user()->restaurant;

        $restaurant->update([
            'busy_mode' => $request->boolean('busy_mode'),
            'busy_eta_multiplier' => $request->filled('busy_eta_multiplier')
                ? round((float) $request->input('busy_eta_multiplier'), 1)
                : $restaurant->busyEtaMultiplier(),
        ]);

        return back()->with('success', $restaurant->isBusy()
            ? 'Busy mode ON — customer ETAs are extended.'
            : 'Busy mode OFF — ETAs back to normal.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preparation_time' => 'nullable|integer|min:1|max:240',
        ]);

        $data = $request->only([
            'name',
            'category_id',
            'price',
            'description',
            'preparation_time',
        ]);
        $data['restaurant_id'] = Auth::user()->restaurant_id;
        $data['is_available'] = $request->has('is_available') ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu', 'public');
        }

        MenuItem::create($data);

        return back()->with('success', 'Menu item added successfully!');
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'is_available' => 'boolean',
        ]);

        $data = $request->only([
            'name',
            'category_id',
            'price',
            'description',
            'preparation_time',
        ]);
        $data['is_available'] = $request->has('is_available') ? 1 : 0;

        if ($request->filled('preparation_time')) {
            $data['preparation_time'] = (int) $request->input('preparation_time');
        } elseif ($request->exists('preparation_time') && $request->input('preparation_time') === '') {
            $data['preparation_time'] = null;
        }

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $data['image'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem->update($data);

        return back()->with('success', 'Menu item updated successfully!');
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }
        $menuItem->delete();

        return back()->with('success', 'Menu item deleted successfully!');
    }
}
