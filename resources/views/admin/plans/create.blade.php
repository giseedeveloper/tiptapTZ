<x-admin-layout>
    <x-slot name="header">New Plan</x-slot>
    @include('admin.partials.flash')

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.plans.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-fin-primary hover:text-fin-lavender transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                Back to Plans
            </a>
        </div>
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <div class="mb-8">
                <h2 class="text-2xl font-black text-white tracking-tight">Create subscription plan</h2>
                <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Restaurants choose this after approval</p>
            </div>

            <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-8">
                @csrf
                @include('admin.plans._form')

                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <a href="{{ route('admin.plans.index') }}" class="px-6 py-3 glass text-white/60 rounded-xl font-semibold text-sm">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-sm">Create plan</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
