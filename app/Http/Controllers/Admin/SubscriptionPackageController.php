<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPackageRequest;
use App\Http\Requests\Admin\UpdateSubscriptionPackageRequest;
use App\Models\AdminActivityLog;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionPackageController extends Controller
{
    public function index(): View
    {
        $packages = SubscriptionPackage::query()
            ->withCount('restaurants')
            ->ordered()
            ->get();

        return view('admin.plans.index', compact('packages'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(StoreSubscriptionPackageRequest $request): RedirectResponse
    {
        $data = $this->preparedData($request->validated());

        $package = SubscriptionPackage::query()->create($data);

        AdminActivityLog::log(
            'subscription_package.created',
            SubscriptionPackage::class,
            (int) $package->id,
            null,
            ['name' => $package->name, 'price' => $package->price],
        );

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan "'.$package->name.'" created successfully.');
    }

    public function edit(SubscriptionPackage $plan): View
    {
        return view('admin.plans.edit', ['package' => $plan]);
    }

    public function update(UpdateSubscriptionPackageRequest $request, SubscriptionPackage $plan): RedirectResponse
    {
        $before = $plan->only(['name', 'price', 'is_active', 'is_featured']);

        $plan->update($this->preparedData($request->validated()));

        AdminActivityLog::log(
            'subscription_package.updated',
            SubscriptionPackage::class,
            (int) $plan->id,
            $before,
            $plan->only(['name', 'price', 'is_active', 'is_featured']),
        );

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan "'.$plan->name.'" updated successfully.');
    }

    public function destroy(SubscriptionPackage $plan): RedirectResponse
    {
        if ($plan->restaurants()->exists()) {
            return redirect()
                ->route('admin.plans.index')
                ->with('error', 'Cannot delete "'.$plan->name.'" — it is assigned to one or more restaurants.');
        }

        $name = $plan->name;
        $id = (int) $plan->id;
        $plan->delete();

        AdminActivityLog::log(
            'subscription_package.deleted',
            SubscriptionPackage::class,
            $id,
            ['name' => $name],
            null,
        );

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan "'.$name.'" deleted.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function preparedData(array $validated): array
    {
        $validated['currency'] = $validated['currency'] ?? config('tiptap.currency_code', 'TZS');
        $validated['features'] = collect($validated['features'] ?? [])
            ->map(fn ($f) => is_string($f) ? trim($f) : '')
            ->filter()
            ->values()
            ->all();
        $validated['capabilities'] = collect($validated['capabilities'] ?? [])
            ->filter(fn ($c) => array_key_exists($c, \App\Models\SubscriptionPackage::CAPABILITIES))
            ->values()
            ->all();
        $validated['trial_days'] = $validated['trial_days'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
