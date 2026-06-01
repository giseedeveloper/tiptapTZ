<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function index()
    {
        $restaurant = Auth::user()->restaurant;

        return view('manager.api.index', compact('restaurant'));
    }

    /**
     * Update Customer Support Phone (shown on WhatsApp bot)
     */
    public function updateSupportPhone(Request $request)
    {
        $request->validate([
            'support_phone' => 'nullable|string|max:20',
        ]);

        $restaurant = Auth::user()->restaurant;
        $restaurant->update([
            'support_phone' => $request->input('support_phone') ?: null,
        ]);

        return back()->with('success', 'Customer support number updated. It will appear on the WhatsApp menu when set.');
    }
}
