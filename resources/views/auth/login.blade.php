<x-guest-layout title="TIPTAP | Login">
    <div class="relative mx-auto w-full">
        <div class="text-center mb-5">
            <h2 class="text-xl sm:text-2xl font-black text-[#12141C] tracking-tight leading-tight">Welcome back!</h2>
            <p class="text-[#64708B] font-medium mt-1.5 text-sm">Sign in to your TIPTAP account</p>
        </div>

        <x-auth-social-providers intent="login" role="manager" locale="en" />

        <x-auth-email-divider label="Or continue with email" />

        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div class="group space-y-1.5">
                <label for="email" class="text-sm font-semibold text-[#12141C]">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"
                           class="block w-full pl-10 pr-4 py-2.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-sm placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="group space-y-1.5">
                <div class="flex justify-between items-center">
                    <label for="password" class="text-sm font-semibold text-[#12141C]">Password</label>
                    @if (Route::has('password.request'))
                        <a class="text-xs font-semibold text-[#8C71F6] hover:text-[#6D52E8] transition-colors" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#64708B]/45 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                           class="block w-full pl-10 pr-4 py-2.5 bg-white border border-[#E8E8ED] rounded-xl font-medium text-[#12141C] text-sm placeholder-[#64708B]/45 focus:ring-2 focus:ring-[#8C71F6]/20 focus:border-[#8C71F6]/40 transition-all">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center cursor-pointer gap-2">
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-[#DDD7FE] text-[#6D52E8] focus:ring-[#8C71F6]" name="remember">
                    <span class="text-sm font-medium text-[#64708B]">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-fin w-full py-2.5 text-white rounded-xl font-bold text-sm shadow-[0_4px_14px_rgba(109,82,232,0.35)]">
                Sign in
            </button>
        </form>

        <p class="mt-5 text-center text-xs sm:text-sm text-[#64708B] leading-relaxed">
            Don't have an account?
            <a href="{{ route('restaurant.register') }}" class="font-semibold text-[#6D52E8] hover:text-[#8C71F6]">Register restaurant</a>
            <span class="text-[#CBD5E1] mx-1">·</span>
            <a href="{{ route('waiter.register') }}" class="font-semibold text-[#6D52E8] hover:text-[#8C71F6]">Register waiter</a>
        </p>
    </div>
</x-guest-layout>
