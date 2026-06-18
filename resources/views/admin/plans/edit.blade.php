<x-admin-layout>
    <x-slot name="header">Edit Plan</x-slot>
    @include('admin.partials.flash')

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.plans.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-fin-primary hover:text-fin-lavender transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                Back to Plans
            </a>
        </div>
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <div class="mb-8 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-white tracking-tight">Edit "{{ $package->name }}"</h2>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">{{ $package->restaurants_count ?? $package->restaurants()->count() }} restaurant(s) on this plan</p>
                </div>
            </div>

            <form action="{{ route('admin.plans.update', $package) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                @include('admin.plans._form')

                <div class="flex justify-between items-center gap-3 pt-4 border-t border-white/10">
                    <button type="submit" form="plan-delete-form" onclick="return confirm('Delete this plan? This cannot be undone.')" class="px-5 py-3 bg-rose-500/15 border border-rose-500/30 text-rose-300 rounded-xl font-bold text-sm hover:bg-rose-500/25 transition">Delete plan</button>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.plans.index') }}" class="px-6 py-3 glass text-white/60 rounded-xl font-semibold text-sm">Cancel</a>
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-sm">Save changes</button>
                    </div>
                </div>
            </form>

            <form id="plan-delete-form" action="{{ route('admin.plans.destroy', $package) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-admin-layout>
