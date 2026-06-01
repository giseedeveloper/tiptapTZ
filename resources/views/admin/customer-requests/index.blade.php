<x-admin-layout>
    <x-slot name="header">Customer Requests</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @include('admin.partials.page-hero', [
        'eyebrow' => 'Operations',
        'title' => 'Customer Requests',
        'subtitle' => 'Call waiter, bill requests, and table assistance across all venues.',
        'accent' => 'pink',
    ])

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        @include('admin.partials.stat-chip', ['label' => 'Pending now', 'value' => number_format($pendingCount), 'tone' => 'amber'])
        @include('admin.partials.stat-chip', ['label' => 'Total listed', 'value' => number_format($requests->total()), 'tone' => 'pink'])
        @include('admin.partials.stat-chip', ['label' => 'This page', 'value' => $requests->count(), 'tone' => 'cyan'])
    </div>

    <div class="glass-card admin-data-panel rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="min-w-[140px]">
                    <label class="text-[10px] font-bold uppercase text-white/40 mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                        <option value="">All</option>
                        @foreach(['pending','completed'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="text-[10px] font-bold uppercase text-white/40 mb-1 block">Type</label>
                    <input type="text" name="type" value="{{ request('type') }}" placeholder="call_waiter…" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                </div>
                <div class="min-w-[180px]">
                    <label class="text-[10px] font-bold uppercase text-white/40 mb-1 block">Restaurant</label>
                    <select name="restaurant_id" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                        <option value="">All</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl text-sm font-semibold">Filter</button>
                <a href="{{ route('admin.customer-requests.index') }}" class="px-5 py-2.5 bg-white/10 text-white rounded-xl text-sm border border-white/10">Clear</a>
            </form>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">ID</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Restaurant</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Table</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Type</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase tracking-widest">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($requests as $req)
                        <tr class="admin-table-row">
                            <td class="px-6 py-4 text-white font-mono text-sm">#{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4 text-white/80 text-sm">{{ $req->restaurant?->name }}</td>
                            <td class="px-6 py-4 text-white/80 text-sm font-semibold">{{ $req->table_number ?? $req->table_id }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 rounded-lg text-xs bg-white/5 text-white/60">{{ $req->type }}</span></td>
                            <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $req->status === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">{{ $req->status }}</span></td>
                            <td class="px-6 py-4 text-right">
                                @if($req->status === 'pending')
                                    <form method="POST" action="{{ route('admin.customer-requests.complete', $req->id) }}" class="inline">@csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-violet-600/30 text-violet-200 text-xs font-semibold hover:bg-violet-600/50 transition-all">Complete</button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-white/30 uppercase font-bold">Done</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white/30"><i data-lucide="bell" class="w-8 h-8"></i></div>
                                <p class="text-white font-bold">No requests found</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="p-6 border-t border-white/5">{{ $requests->links() }}</div>
        @endif
    </div>
</x-admin-layout>
