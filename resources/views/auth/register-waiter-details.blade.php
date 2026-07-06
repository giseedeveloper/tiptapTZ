<x-guest-layout title="TIPTAP | Waiter Details" :wide="true">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-[#F5F3FF] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#6D52E8] border border-[#DDD7FE] mb-4">
                Step 2 of 2
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#12141C] tracking-tight">Your profile</h1>
            <p class="text-[#64708B] text-sm mt-2">Tell us a bit about yourself</p>
        </div>

        <div class="mb-6 rounded-2xl border border-[#DDD7FE] bg-[#FAFAFE] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-1">Account email</p>
            <p class="font-semibold text-[#12141C]">{{ $waiterEmail }}</p>
        </div>

        <form method="POST" action="{{ route('waiter.register.details.store') }}" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium space-y-1" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">First name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autofocus placeholder="First name"
                           class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <label for="last_name" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Last name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required placeholder="Last name"
                           class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="phone" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="e.g. 071 234 5678"
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <label for="location" class="text-xs font-bold uppercase tracking-wider text-[#64708B] mb-2 block">Location (optional)</label>
                <input id="location" name="location" type="text" value="{{ old('location') }}" placeholder="e.g. Dar es Salaam"
                       class="block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent">
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('waiter.register') }}" class="text-sm font-semibold text-[#64708B] hover:text-[#12141C] transition-colors">
                    Back
                </a>
                <button type="submit" class="btn-fin flex-1 py-4 text-white rounded-xl font-bold text-base">
                    Create account
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
