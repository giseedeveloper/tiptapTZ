@php
    $package = $package ?? null;
    $features = old('features', $package?->features ?? ['']);
    if (empty($features)) { $features = ['']; }
    $currency = old('currency', $package?->currency ?? config('tiptap.currency_code', 'TZS'));
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Plan name</label>
        <input type="text" name="name" value="{{ old('name', $package?->name) }}" required placeholder="e.g. Business"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Tagline</label>
        <input type="text" name="tagline" value="{{ old('tagline', $package?->tagline) }}" placeholder="e.g. Most popular for growing venues"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('tagline')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Price</label>
        <div class="flex">
            <span class="inline-flex items-center px-3 bg-white/10 border border-r-0 border-white/10 rounded-l-xl text-sm text-white/60">{{ $currency }}</span>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $package?->price ?? 0) }}" required
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-r-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        </div>
        @error('price')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Currency</label>
        <input type="text" name="currency" value="{{ $currency }}" maxlength="8"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('currency')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Billing period</label>
        @php $bp = old('billing_period', $package?->billing_period ?? 'monthly'); @endphp
        <select name="billing_period" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
            <option value="monthly" {{ $bp === 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="yearly" {{ $bp === 'yearly' ? 'selected' : '' }}>Yearly</option>
            <option value="trial" {{ $bp === 'trial' ? 'selected' : '' }}>Free trial</option>
            <option value="one_time" {{ $bp === 'one_time' ? 'selected' : '' }}>One-time</option>
        </select>
        @error('billing_period')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Trial days (if free trial)</label>
        <input type="number" min="0" max="365" name="trial_days" value="{{ old('trial_days', $package?->trial_days ?? 0) }}"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('trial_days')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Table limit (blank = unlimited)</label>
        <input type="number" min="1" name="table_limit" value="{{ old('table_limit', $package?->table_limit) }}" placeholder="Unlimited"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('table_limit')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Waiter limit (blank = unlimited)</label>
        <input type="number" min="1" name="waiter_limit" value="{{ old('waiter_limit', $package?->waiter_limit) }}" placeholder="Unlimited"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('waiter_limit')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Capabilities (enforced features)</label>
        <p class="text-[11px] text-white/35 -mt-1">Only checked capabilities are unlocked for restaurants on this plan.</p>
        @php $selectedCaps = old('capabilities', $package?->capabilities ?? []); @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            @foreach (\App\Models\SubscriptionPackage::CAPABILITIES as $capKey => $capLabel)
                <label class="flex items-center gap-3 p-3 bg-white/5 rounded-xl border border-white/10 cursor-pointer">
                    <input type="checkbox" name="capabilities[]" value="{{ $capKey }}" {{ in_array($capKey, $selectedCaps, true) ? 'checked' : '' }} class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
                    <span class="text-sm text-white">{{ $capLabel }}</span>
                </label>
            @endforeach
        </div>
        @error('capabilities')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $package?->sort_order ?? 0) }}"
               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        @error('sort_order')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Description</label>
        <textarea name="description" rows="2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">{{ old('description', $package?->description) }}</textarea>
        @error('description')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @php
        $catalog = \App\Models\SubscriptionPackage::FEATURE_CATALOG;
        $selectedFeatures = array_values(array_filter($features, fn ($f) => is_string($f) && trim($f) !== ''));
        $customFeatures = array_values(array_diff($selectedFeatures, $catalog));
        if (empty($customFeatures)) { $customFeatures = ['']; }
    @endphp
    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Features (tick what's included in this plan)</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            @foreach ($catalog as $catalogFeature)
                <label class="flex items-center gap-3 p-3 bg-white/5 rounded-xl border border-white/10 cursor-pointer">
                    <input type="checkbox" name="features[]" value="{{ $catalogFeature }}" {{ in_array($catalogFeature, $selectedFeatures, true) ? 'checked' : '' }} class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
                    <span class="text-sm text-white">{{ $catalogFeature }}</span>
                </label>
            @endforeach
        </div>
        @error('features')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 lg:col-span-2">
        <label class="text-[10px] font-bold uppercase text-white/40">Custom features (optional)</label>
        <p class="text-[11px] text-white/35 -mt-1">Add anything not in the list above.</p>
        <div id="plan-features-list" class="space-y-2 mt-1">
            @foreach ($customFeatures as $feature)
                <div class="flex gap-2 plan-feature-row">
                    <input type="text" name="features[]" value="{{ $feature }}" placeholder="e.g. Loyalty rewards"
                           class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
                    <button type="button" class="plan-feature-remove px-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500/25 transition shrink-0">&times;</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="plan-feature-add" class="mt-1 text-xs font-bold text-fin-primary hover:text-fin-lavender transition">+ Add custom feature</button>
    </div>

    <label class="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10 cursor-pointer">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $package?->is_featured) ? 'checked' : '' }} class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
        <span class="text-sm text-white">Featured (Most popular)</span>
    </label>

    <label class="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package?->is_active ?? true) ? 'checked' : '' }} class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
        <span class="text-sm text-white">Active (visible to restaurants)</span>
    </label>
</div>

<script>
(function () {
    const list = document.getElementById('plan-features-list');
    const addBtn = document.getElementById('plan-feature-add');
    if (!list || !addBtn) return;

    const rowHtml = () =>
        '<div class="flex gap-2 plan-feature-row">' +
        '<input type="text" name="features[]" placeholder="e.g. Loyalty rewards" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">' +
        '<button type="button" class="plan-feature-remove px-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500/25 transition shrink-0">&times;</button>' +
        '</div>';

    addBtn.addEventListener('click', function () {
        list.insertAdjacentHTML('beforeend', rowHtml());
    });

    list.addEventListener('click', function (e) {
        if (e.target.classList.contains('plan-feature-remove')) {
            const rows = list.querySelectorAll('.plan-feature-row');
            if (rows.length > 1) {
                e.target.closest('.plan-feature-row').remove();
            } else {
                e.target.closest('.plan-feature-row').querySelector('input').value = '';
            }
        }
    });
})();
</script>
