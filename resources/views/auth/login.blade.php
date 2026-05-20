<x-guest-layout title="TIPTAP | Login">
    <div class="relative">
        <div class="text-center mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-black text-[#12141C] tracking-tight">Welcome Back!</h2>
            <p class="text-[#64708B] font-medium mt-2 text-sm sm:text-base">Sign in to your TIPTAP account</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-6">
            @csrf

            <div class="group">
                <label for="email" class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#64708B] mb-1.5 sm:mb-2 block">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#64708B]/50 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"
                           class="block w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] text-sm sm:text-base placeholder-[#64708B]/50 focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent transition-all">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="group">
                <div class="flex justify-between items-center mb-1.5 sm:mb-2">
                    <label for="password" class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#64708B]">Password</label>
                    @if (Route::has('password.request'))
                        <a class="text-[10px] sm:text-xs font-bold text-[#8C71F6] hover:text-[#6D52E8] transition-colors" href="{{ route('password.request') }}">
                            Forgot Password?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#64708B]/50 group-focus-within:text-[#8C71F6] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                           class="block w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] text-sm sm:text-base placeholder-[#64708B]/50 focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent transition-all">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="w-4 h-4 sm:w-5 sm:h-5 rounded bg-[#F5F3FF] border-[#DDD7FE] text-[#6D52E8] focus:ring-[#8C71F6] focus:ring-offset-0 transition-all" name="remember">
                    <span class="ms-2 sm:ms-3 text-xs sm:text-sm font-medium text-[#64708B]">Remember me</span>
                </label>
            </div>

            <div class="pt-1 sm:pt-2">
                <button type="submit" class="btn-fin w-full py-3 sm:py-4 text-white rounded-xl font-bold text-base sm:text-lg flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
