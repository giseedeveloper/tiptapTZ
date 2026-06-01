<x-admin-layout>
    <x-slot name="header">Add Restaurant</x-slot>
    @include('admin.partials.flash')

    <div class="max-w-3xl mx-auto">
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <div class="mb-8">
                <h2 class="text-2xl font-black text-white tracking-tight">New Restaurant Partner</h2>
                <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Creates venue + manager login</p>
            </div>

            <form action="{{ route('admin.restaurants.store') }}" method="POST" class="space-y-8">
                @csrf

                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest mb-4">Restaurant</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Restaurant name</label>
                            <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('restaurant_name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Location</label>
                            <input type="text" name="location" value="{{ old('location') }}" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('location')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('phone')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10 md:col-span-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-white">Active immediately</span>
                        </label>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/10">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest mb-4">Manager account</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Full name</label>
                            <input type="text" name="manager_name" value="{{ old('manager_name') }}" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('manager_name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Email</label>
                            <input type="email" name="manager_email" value="{{ old('manager_email') }}" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('manager_email')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Password</label>
                            <input type="password" name="manager_password" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('manager_password')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-white/40">Confirm password</label>
                            <input type="password" name="manager_password_confirmation" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.restaurants.index') }}" class="px-6 py-3 glass text-white/60 rounded-xl font-semibold text-sm">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-sm">Create restaurant</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
