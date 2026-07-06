<x-guest-layout title="TIPTAP | Complete Waiter Registration" :wide="true">
    @php
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $defaultFirst = $nameParts[0] ?? '';
        $defaultLast = $nameParts[1] ?? '';
    @endphp

    <div class="max-w-xl mx-auto">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-[#F5F3FF] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#6D52E8] border border-[#DDD7FE] mb-4">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                Account connected
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#12141C] tracking-tight">Your profile</h1>
            <p class="text-[#64708B] text-sm mt-2">Your sign-in is set up. Add your details below.</p>
        </div>

        <div class="mb-6 rounded-2xl border border-[#DDD7FE] bg-[#FAFAFE] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-1">Account email</p>
            <p class="font-semibold text-[#12141C]">{{ $user->email }}</p>
        </div>

        <form method="POST" action="{{ route('waiter.oauth.complete.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">First name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $defaultFirst) }}" required
                           class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <label for="last_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Last name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $defaultLast) }}" required
                           class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="phone" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <label for="location" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Location (optional)</label>
                <input id="location" name="location" type="text" value="{{ old('location', $user->location) }}" placeholder="e.g. Dar es Salaam"
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>

            <button type="submit" class="btn-fin w-full py-4 text-white rounded-xl font-bold text-base">
                Create account
            </button>
        </form>
    </div>
</x-guest-layout>
