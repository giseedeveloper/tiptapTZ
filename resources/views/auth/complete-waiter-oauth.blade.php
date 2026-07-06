@php
    $nameParts = preg_split('/\s+/', trim($user->name), 2);
    $defaultFirst = $nameParts[0] ?? '';
    $defaultLast = $nameParts[1] ?? '';
@endphp

<x-guest-layout title="TIPTAP | Your Profile">
    <div class="relative mx-auto w-full">
        <div class="mb-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <span class="text-xs font-semibold text-[#6D52E8]">Step 2 of 2</span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    Google connected
                </span>
            </div>
            <div class="h-1.5 rounded-full bg-[#EDE9FE] overflow-hidden mb-5">
                <div class="h-full w-full rounded-full bg-gradient-to-r from-[#8C71F6] to-[#6D52E8]"></div>
            </div>

            <div class="text-center mb-5">
                <h1 class="text-xl sm:text-2xl font-black text-[#12141C] tracking-tight leading-tight">Your profile</h1>
                <p class="text-[#64708B] font-medium mt-1.5 text-sm">Almost done — add your contact details</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-[#E8E8ED] bg-white px-4 py-3 mb-5 shadow-sm">
                <div class="h-11 w-11 shrink-0 rounded-full bg-gradient-to-br from-[#8C71F6] to-[#6D52E8] flex items-center justify-center text-white font-bold text-sm shadow-md shadow-[#8C71F6]/25">
                    {{ strtoupper(substr($user->name ?: $user->email, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-[#12141C] truncate">{{ $user->name ?: 'Waiter' }}</p>
                    <p class="text-xs text-[#64708B] truncate">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        @include('partials.auth-waiter-details-form', [
            'action' => route('waiter.oauth.complete.store'),
            'firstName' => old('first_name', $defaultFirst),
            'lastName' => old('last_name', $defaultLast),
            'phone' => old('phone', $user->phone),
            'location' => old('location', $user->location),
            'locationPlaceholder' => 'e.g. Dar es Salaam',
            'phonePlaceholder' => 'e.g. 071 234 5678',
            'autofocusFirst' => false,
            'showBack' => false,
        ])
    </div>
</x-guest-layout>
