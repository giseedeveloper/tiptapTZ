<x-guest-layout title="TIPTAP | Complete Restaurant Registration" :wide="true">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-[#F5F3FF] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#6D52E8] border border-[#DDD7FE] mb-4">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                Account connected
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#12141C] tracking-tight">Restaurant details</h1>
            <p class="text-[#64708B] text-sm mt-2">Your sign-in is set up. Add your restaurant info below.</p>
        </div>

        <div class="mb-6 rounded-2xl border border-[#DDD7FE] bg-[#FAFAFE] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-1">Account email</p>
            <p class="font-semibold text-[#12141C]">{{ $user->email }}</p>
        </div>

        <form method="POST" action="{{ route('restaurant.oauth.complete.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="manager_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Manager full name</label>
                <input id="manager_name" name="manager_name" type="text" value="{{ old('manager_name', $user->name) }}" required
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('manager_name')" class="mt-2" />
            </div>

            <div>
                <label for="restaurant_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Restaurant name</label>
                <input id="restaurant_name" name="restaurant_name" type="text" value="{{ old('restaurant_name') }}" required
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('restaurant_name')" class="mt-2" />
            </div>

            <div>
                <label for="phone" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Restaurant phone</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <label for="location" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Location / city</label>
                <input id="location" name="location" type="text" value="{{ old('location') }}" required placeholder="e.g. Dar es Salaam"
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>

            <button type="submit" class="btn-fin w-full py-4 text-white rounded-xl font-bold text-base">
                Complete registration
            </button>
        </form>
    </div>
</x-guest-layout>
