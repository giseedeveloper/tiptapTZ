<x-guest-layout title="TIPTAP | Register Restaurant">
    <div class="relative max-w-[420px] mx-auto w-full">
        <div class="text-center mb-7">
            <h1 class="text-[1.65rem] sm:text-3xl font-black text-[#12141C] tracking-tight leading-tight">Register Restaurant</h1>
            <p class="text-[#64708B] font-medium mt-2 text-sm">Get started with Google or email</p>
        </div>

        <x-auth-social-providers intent="register" role="manager" locale="en" />

        <x-auth-email-divider label="Or register with email" />

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium space-y-1" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('restaurant.register.credentials') }}" class="space-y-5">
            @csrf

            <div class="group space-y-2">
                <label for="manager_email" class="text-sm font-semibold text-[#12141C]">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input id="manager_email" type="email" name="manager_email" value="{{ old('manager_email') }}" required autofocus autocomplete="username" placeholder="manager@restaurant.com"
                           class="block w-full pl-12 pr-4 py-3.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-[15px] placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all">
                </div>
                <x-input-error :messages="$errors->get('manager_email')" class="mt-1" />
            </div>

            <div class="group space-y-2">
                <label for="manager_password" class="text-sm font-semibold text-[#12141C]">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="manager_password" type="password" name="manager_password" required autocomplete="new-password" placeholder="At least 8 characters"
                           class="block w-full pl-12 pr-4 py-3.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-[15px] placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all">
                </div>
                <x-input-error :messages="$errors->get('manager_password')" class="mt-1" />
            </div>

            <div class="group space-y-2">
                <label for="manager_password_confirmation" class="text-sm font-semibold text-[#12141C]">Confirm password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <input id="manager_password_confirmation" type="password" name="manager_password_confirmation" required autocomplete="new-password" placeholder="Repeat password"
                           class="block w-full pl-12 pr-4 py-3.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-[15px] placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all">
                </div>
            </div>

            <button type="submit" class="btn-fin w-full py-3.5 text-white rounded-xl font-bold text-base shadow-[0_4px_14px_rgba(109,82,232,0.35)]">
                Continue
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-[#64708B]">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-[#6D52E8] hover:text-[#8C71F6]">Sign in here</a>
        </p>
    </div>
</x-guest-layout>
