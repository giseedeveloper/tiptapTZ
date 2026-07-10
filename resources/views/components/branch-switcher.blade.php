@if(auth()->check() && auth()->user()->isBranchManager() && ($accessibleBranches ?? collect())->count() > 0)
    <form method="POST" action="{{ route('manager.switch-branch') }}" class="relative">
        @csrf
        <label class="sr-only" for="branch-switcher">Switch branch</label>
        <select
            id="branch-switcher"
            name="branch_id"
            onchange="this.form.submit()"
            class="appearance-none rounded-xl border border-white/10 bg-white/5 pl-4 pr-10 py-2.5 text-xs font-semibold text-white/90 focus:outline-none focus:ring-2 focus:ring-violet-500 min-w-[180px] max-w-[240px]"
        >
            <option value="all" @selected(empty($activeBranchId))>All Branches</option>
            @foreach($accessibleBranches as $branch)
                <option value="{{ $branch->id }}" @selected((int) ($activeBranchId ?? 0) === (int) $branch->id)>
                    {{ $branch->displayName() }}
                </option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-white/40">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </div>
    </form>
@endif
