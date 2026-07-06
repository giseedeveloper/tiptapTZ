<x-guest-layout title="TIPTAP | Your Profile">
    <div class="relative mx-auto w-full">
        <div class="mb-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <span class="text-xs font-semibold text-[#6D52E8]">Step 2 of 2</span>
            </div>
            <div class="h-1.5 rounded-full bg-[#EDE9FE] overflow-hidden mb-5">
                <div class="h-full w-full rounded-full bg-gradient-to-r from-[#8C71F6] to-[#6D52E8]"></div>
            </div>

            <div class="text-center mb-5">
                <h1 class="text-xl sm:text-2xl font-black text-[#12141C] tracking-tight leading-tight">Your profile</h1>
                <p class="text-[#64708B] font-medium mt-1.5 text-sm">Tell us a bit about yourself</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-[#E8E8ED] bg-white px-4 py-3 mb-5 shadow-sm">
                <div class="h-11 w-11 shrink-0 rounded-full bg-[#F5F3FF] border border-[#DDD7FE] flex items-center justify-center text-[#6D52E8]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-[#64708B]">Signing up as</p>
                    <p class="text-sm font-semibold text-[#12141C] truncate">{{ $waiterEmail }}</p>
                </div>
            </div>
        </div>

        @include('partials.auth-waiter-details-form', [
            'action' => route('waiter.register.details.store'),
            'firstName' => old('first_name'),
            'lastName' => old('last_name'),
            'phone' => old('phone'),
            'location' => old('location'),
            'locationPlaceholder' => 'e.g. Dar es Salaam',
            'phonePlaceholder' => 'e.g. 071 234 5678',
            'autofocusFirst' => true,
            'showBack' => true,
        ])
    </div>
</x-guest-layout>
