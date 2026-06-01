<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\CustomerRequest;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = CustomerRequest::withoutGlobalScope(RestaurantScope::class)
            ->with(['restaurant', 'waiter', 'table'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->integer('restaurant_id'));
        }

        $requests = $query->paginate(25)->withQueryString();
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        $pendingCount = CustomerRequest::withoutGlobalScope(RestaurantScope::class)
            ->where('status', 'pending')
            ->count();

        return view('admin.customer-requests.index', compact('requests', 'restaurants', 'pendingCount'));
    }

    public function complete(string $id): RedirectResponse
    {
        $customerRequest = CustomerRequest::withoutGlobalScope(RestaurantScope::class)->findOrFail($id);
        $customerRequest->update(['status' => 'completed']);

        AdminActivityLog::log(
            'customer_request.completed',
            CustomerRequest::class,
            (int) $customerRequest->id,
            ['status' => 'pending'],
            ['status' => 'completed'],
        );

        return back()->with('success', 'Customer request marked as completed.');
    }
}
