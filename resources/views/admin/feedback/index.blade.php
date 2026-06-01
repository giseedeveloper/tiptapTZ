<x-admin-layout>
    <x-slot name="header">Feedback</x-slot>
    <div class="glass-card rounded-2xl overflow-hidden border border-white/10">
        <div class="p-6 border-b border-white/5">
            <h2 class="text-xl font-black text-white">All Feedback</h2>
            <p class="text-sm text-amber-400 mt-1">Average rating: {{ number_format($avgRating, 1) }} / 5</p>
            <form method="GET" class="mt-4 flex flex-wrap gap-3">
                <select name="restaurant_id" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    <option value="">All restaurants</option>
                    @foreach($restaurants as $r)<option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>@endforeach
                </select>
                <select name="rating" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    <option value="">All ratings</option>
                    @for($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} stars</option>@endfor
                </select>
                <button type="submit" class="px-5 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-semibold">Filter</button>
            </form>
        </div>
        <div class="divide-y divide-white/5">
            @forelse($feedback as $item)
                <div class="p-6 hover:bg-white/5">
                    <div class="flex flex-wrap justify-between gap-2 mb-2">
                        <span class="text-amber-400 font-bold">{{ str_repeat('★', (int) $item->rating) }}</span>
                        <span class="text-xs text-white/40">{{ $item->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-white/90 text-sm">{{ $item->comment ?: '—' }}</p>
                    <p class="text-xs text-white/40 mt-2">{{ $item->restaurant?->name }} · Waiter: {{ $item->waiter?->name ?? '—' }} · <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-violet-400">Order #{{ $item->order_id }}</a></p>
                </div>
            @empty
                <p class="p-12 text-center text-white/40">No feedback yet</p>
            @endforelse
        </div>
        @if($feedback->hasPages())<div class="p-4 border-t border-white/5">{{ $feedback->links() }}</div>@endif
    </div>
</x-admin-layout>
