@props([
    'action',
    'firstName' => old('first_name'),
    'lastName' => old('last_name'),
    'phone' => old('phone'),
    'location' => old('location'),
    'locationPlaceholder' => 'e.g. Dar es Salaam',
    'phonePlaceholder' => 'e.g. 071 234 5678',
    'autofocusFirst' => false,
    'showBack' => false,
])

@php
    $inputClass = 'block w-full pl-10 pr-4 py-2.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-sm placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all';
    $iconClass = 'h-4 w-4 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    @if ($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium space-y-1" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="group space-y-1.5">
            <label for="first_name" class="text-sm font-semibold text-[#12141C]">First name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="first_name" name="first_name" type="text" value="{{ $firstName }}" required
                       @if($autofocusFirst) autofocus @endif placeholder="First name"
                       class="{{ $inputClass }}">
            </div>
            <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
        </div>

        <div class="group space-y-1.5">
            <label for="last_name" class="text-sm font-semibold text-[#12141C]">Last name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="last_name" name="last_name" type="text" value="{{ $lastName }}" required placeholder="Last name"
                       class="{{ $inputClass }}">
            </div>
            <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="group space-y-1.5">
            <label for="phone" class="text-sm font-semibold text-[#12141C]">Phone</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <input id="phone" name="phone" type="tel" value="{{ $phone }}" required placeholder="{{ $phonePlaceholder }}"
                       class="{{ $inputClass }}">
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div class="group space-y-1.5">
            <label for="location" class="text-sm font-semibold text-[#12141C]">City <span class="font-normal text-[#64708B]">(optional)</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <input id="location" name="location" type="text" value="{{ $location }}" placeholder="{{ $locationPlaceholder }}"
                       class="{{ $inputClass }}">
            </div>
            <x-input-error :messages="$errors->get('location')" class="mt-1" />
        </div>
    </div>

    <p class="text-xs text-[#64708B] leading-relaxed">
        You'll receive a unique waiter number instantly. A restaurant manager will link you to their team.
    </p>

    <div class="@if($showBack) flex items-center gap-3 pt-1 @else pt-1 @endif">
        @if ($showBack)
            <a href="{{ route('waiter.register') }}" class="text-sm font-semibold text-[#64708B] hover:text-[#12141C] transition-colors shrink-0">
                Back
            </a>
        @endif
        <button type="submit" class="btn-fin @if($showBack) flex-1 @else w-full @endif py-2.5 text-white rounded-xl font-bold text-sm shadow-[0_4px_14px_rgba(109,82,232,0.35)]">
            Create account
        </button>
    </div>
</form>
