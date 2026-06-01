<x-admin-layout>
    <x-slot name="header">Activity Log</x-slot>
    <div class="glass-card rounded-2xl overflow-hidden border border-white/10">
        <div class="p-6 border-b border-white/5">
            <h2 class="text-xl font-black text-white">Admin Activity Log</h2>
            <form method="GET" class="mt-4 flex flex-wrap gap-3">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Action filter…" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white min-w-[160px]">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                <button type="submit" class="px-5 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-semibold">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">When</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">User</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Action</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Subject</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 text-xs text-white/50 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-white/80">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-violet-300">{{ $log->action }}</td>
                            <td class="px-6 py-4 text-xs text-white/50">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-white/40">No activity logged</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="p-4 border-t border-white/5">{{ $logs->links() }}</div>@endif
    </div>
</x-admin-layout>
