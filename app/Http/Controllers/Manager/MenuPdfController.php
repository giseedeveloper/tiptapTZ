<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreMenuPdfRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuPdfController extends Controller
{
    public function index(): View
    {
        $restaurant = Restaurant::find(Auth::user()->restaurant_id);

        return view('manager.menu-pdf.index', compact('restaurant'));
    }

    public function store(StoreMenuPdfRequest $request): RedirectResponse
    {
        $restaurant = Restaurant::find(Auth::user()->restaurant_id);

        if (! $restaurant) {
            return back()->with('error', 'Restaurant not found.');
        }

        if ($restaurant->menu_pdf) {
            Storage::disk('public')->delete($restaurant->menu_pdf);
        }

        $path = $request->file('menu_pdf')->store('menu_pdfs', 'public');
        $restaurant->update(['menu_pdf' => $path]);

        return back()->with('success', 'Menu PDF uploaded successfully. Customers will receive it on WhatsApp.');
    }

    public function destroy(): RedirectResponse
    {
        $restaurant = Restaurant::find(Auth::user()->restaurant_id);

        if (! $restaurant) {
            return back()->with('error', 'Restaurant not found.');
        }

        if ($restaurant->menu_pdf) {
            Storage::disk('public')->delete($restaurant->menu_pdf);
            $restaurant->update(['menu_pdf' => null]);
        }

        return back()->with('success', 'Menu PDF removed.');
    }
}
