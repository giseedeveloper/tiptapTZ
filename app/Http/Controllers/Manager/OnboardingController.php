<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\SubscriptionPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Waiting room — shown while a restaurant is pending or rejected.
     */
    public function waiting(Request $request): View|RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return redirect()->route('manager.dashboard');
        }

        if ($restaurant->needsPlanSelection()) {
            return redirect()->route('manager.onboarding.plan');
        }

        if ($restaurant->isFullyOnboarded()) {
            return redirect()->route('manager.dashboard');
        }

        return view('manager.onboarding.waiting', compact('restaurant'));
    }

    /**
     * Lightweight JSON status poll for the waiting page.
     */
    public function status(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;

        return response()->json([
            'status' => $restaurant?->approval_status,
            'needs_plan' => (bool) $restaurant?->needsPlanSelection(),
            'rejection_reason' => $restaurant?->isRejected() ? $restaurant->rejection_reason : null,
            'redirect' => match (true) {
                $restaurant === null => route('manager.dashboard'),
                $restaurant->needsPlanSelection() => route('manager.onboarding.plan'),
                $restaurant->isFullyOnboarded() => route('manager.dashboard'),
                default => null,
            },
        ]);
    }

    /**
     * Plan selection — shown after approval, before the dashboard.
     */
    public function plan(Request $request): View|RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return redirect()->route('manager.dashboard');
        }

        if ($restaurant->isPending() || $restaurant->isRejected()) {
            return redirect()->route('manager.onboarding.waiting');
        }

        if ($restaurant->isFullyOnboarded()) {
            return redirect()->route('manager.dashboard');
        }

        $packages = SubscriptionPackage::query()->active()->ordered()->get();

        return view('manager.onboarding.plan', compact('restaurant', 'packages'));
    }

    /**
     * Persist the chosen plan and activate the restaurant.
     */
    public function selectPlan(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant || $restaurant->isPending() || $restaurant->isRejected()) {
            return redirect()->route('manager.onboarding.waiting');
        }

        $validated = $request->validate([
            'subscription_package_id' => ['required', 'integer', 'exists:subscription_packages,id'],
        ], [
            'subscription_package_id.required' => 'Please choose a plan to continue.',
        ]);

        $package = SubscriptionPackage::query()->active()->findOrFail($validated['subscription_package_id']);

        $restaurant->forceFill([
            'subscription_package_id' => $package->id,
            'plan_selected_at' => now(),
            'approval_status' => Restaurant::STATUS_ACTIVE,
            'is_active' => true,
            'trial_ends_at' => $package->trial_days > 0 ? now()->addDays($package->trial_days) : null,
        ])->save();

        return redirect()
            ->route('manager.dashboard')
            ->with('status', 'Welcome aboard! Your '.$package->name.' plan is now active.');
    }
}
