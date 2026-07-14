<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * List all menu items.
     */
    public function index()
    {
        $menuItems = MenuItem::with('category')->latest()->get()
            ->map(fn (MenuItem $item) => $this->serializeItem($item));

        return response()->json([
            'success' => true,
            'data' => $menuItems,
        ]);
    }

    /**
     * Create a new menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'is_available' => 'boolean',
        ]);

        unset($validated['image']);

        $validated['restaurant_id'] = Auth::user()->restaurant_id;
        $validated['is_available'] = filter_var($request->input('is_available', true), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem = MenuItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => $this->serializeItem($menuItem->fresh() ?? $menuItem),
        ], 201);
    }

    /**
     * Show a specific menu item.
     */
    public function show(int $menu)
    {
        $menuItem = $this->findManagedItem($menu);

        return response()->json([
            'success' => true,
            'data' => $this->serializeItem($menuItem),
        ]);
    }

    /**
     * Update a menu item (including preparation time overrides for customer ETA).
     */
    public function update(Request $request, int $menu)
    {
        $menuItem = $this->findManagedItem($menu);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'is_available' => 'boolean',
        ]);

        if ($request->has('is_available')) {
            $validated['is_available'] = filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN);
        }

        unset($validated['image']);

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $validated['image'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem->fill($validated);
        $menuItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully',
            'data' => $this->serializeItem($menuItem->fresh() ?? $menuItem),
        ]);
    }

    /**
     * Delete a menu item.
     */
    public function destroy(int $menu)
    {
        $menuItem = $this->findManagedItem($menu);

        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }
        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully',
        ]);
    }

    private function findManagedItem(int $menuId): MenuItem
    {
        return MenuItem::withoutGlobalScopes()
            ->with('category')
            ->where('restaurant_id', Auth::user()->restaurant_id)
            ->findOrFail($menuId);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeItem(MenuItem $item): array
    {
        $eta = $item->effectivePreparationMinutes(Auth::user()?->restaurant);

        return [
            'id' => $item->id,
            'restaurant_id' => $item->restaurant_id,
            'category_id' => $item->category_id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
            'image' => $item->image,
            'imageUrl' => $item->imageUrl(),
            'is_available' => (bool) $item->is_available,
            'preparation_time' => $item->preparation_time,
            'eta_minutes' => $eta,
            'eta_label' => 'Ready in ~'.$eta.' min',
            'category' => $item->relationLoaded('category') && $item->category
                ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ]
                : null,
            'created_at' => optional($item->created_at)?->toISOString(),
            'updated_at' => optional($item->updated_at)?->toISOString(),
        ];
    }
}
