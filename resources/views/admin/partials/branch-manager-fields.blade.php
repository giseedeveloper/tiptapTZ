@php
    $selectedBranches = old('branch_ids', $managedBranchIds ?? []);
@endphp

<div x-show="role === 'branch_manager'" x-cloak class="space-y-2">
    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 block">Managed Branches</label>
    <p class="text-[11px] text-white/45 mb-2">Select every branch this manager can access. Primary branch follows the assigned restaurant above.</p>
    <div class="max-h-48 overflow-y-auto rounded-xl border border-white/10 bg-white/5 p-3 space-y-2">
        @foreach($restaurants as $restaurant)
            @php
                $label = $restaurant->branch_name
                    ? $restaurant->name.' — '.$restaurant->branch_name
                    : $restaurant->name;
            @endphp
            <label class="flex items-center gap-3 text-sm text-white/80 cursor-pointer hover:text-white">
                <input type="checkbox"
                       name="branch_ids[]"
                       value="{{ $restaurant->id }}"
                       class="rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500"
                       @checked(in_array($restaurant->id, $selectedBranches, false))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @error('branch_ids') <p class="text-rose-400 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
</div>
