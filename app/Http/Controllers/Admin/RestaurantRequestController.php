<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RestaurantRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'pending')->toString();
        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $query = Restaurant::query()
            ->withCount(['users as managers_count' => fn ($q) => $q->role('manager')])
            ->with('subscriptionPackage')
            ->latest();

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($qry) use ($search) {
                $qry->where('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        $restaurants = $query->paginate(12)->withQueryString();

        $counts = [
            'pending' => Restaurant::query()->where('approval_status', Restaurant::STATUS_PENDING)->count(),
            'approved' => Restaurant::query()->where('approval_status', Restaurant::STATUS_APPROVED)->count(),
            'rejected' => Restaurant::query()->where('approval_status', Restaurant::STATUS_REJECTED)->count(),
        ];

        return view('admin.restaurant-requests.index', compact('restaurants', 'status', 'counts'));
    }

    public function show(Restaurant $restaurant): View
    {
        $restaurant->load(['subscriptionPackage', 'approvedBy']);
        $manager = $restaurant->users()->role('manager')->first();

        return view('admin.restaurant-requests.show', compact('restaurant', 'manager'));
    }

    public function approve(Restaurant $restaurant): RedirectResponse
    {
        if ($restaurant->isLiveActive() || $restaurant->isApproved()) {
            return back()->with('error', 'This restaurant is already approved.');
        }

        $restaurant->markApproved(Auth::id());

        AdminActivityLog::log(
            'restaurant.approved',
            Restaurant::class,
            (int) $restaurant->id,
            ['approval_status' => Restaurant::STATUS_PENDING],
            ['approval_status' => Restaurant::STATUS_APPROVED, 'name' => $restaurant->name],
        );

        return redirect()
            ->route('admin.restaurant-requests.index')
            ->with('success', '"'.$restaurant->name.'" approved. The manager can now choose a plan.');
    }

    public function reject(Restaurant $restaurant, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please provide a reason so the manager knows what to fix.',
        ]);

        $restaurant->markRejected($validated['rejection_reason'], Auth::id());

        AdminActivityLog::log(
            'restaurant.rejected',
            Restaurant::class,
            (int) $restaurant->id,
            ['approval_status' => $restaurant->getOriginal('approval_status')],
            ['approval_status' => Restaurant::STATUS_REJECTED, 'name' => $restaurant->name, 'reason' => $validated['rejection_reason']],
        );

        return redirect()
            ->route('admin.restaurant-requests.index')
            ->with('success', '"'.$restaurant->name.'" was rejected. The manager will see your reason.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $restaurants = Restaurant::query()
            ->whereIn('id', $validated['ids'])
            ->where('approval_status', Restaurant::STATUS_PENDING)
            ->get();

        foreach ($restaurants as $restaurant) {
            $restaurant->markApproved(Auth::id());
            AdminActivityLog::log(
                'restaurant.approved',
                Restaurant::class,
                (int) $restaurant->id,
                ['approval_status' => Restaurant::STATUS_PENDING],
                ['approval_status' => Restaurant::STATUS_APPROVED, 'name' => $restaurant->name, 'bulk' => true],
            );
        }

        return redirect()
            ->route('admin.restaurant-requests.index')
            ->with('success', $restaurants->count().' restaurant(s) approved.');
    }
}
