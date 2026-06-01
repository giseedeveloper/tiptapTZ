<x-admin-layout>
    <x-slot name="header">Global Search</x-slot>

    <div class="glass-card rounded-2xl p-6 border border-white/10 mb-8">
        <form method="GET" action="{{ route('admin.search.index') }}" class="flex flex-col sm:flex-row gap-4">
            <input type="search" name="q" value="{{ $query }}" placeholder="Search restaurants, users, orders, waiters, payment refs…" autofocus
                   class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 focus:ring-2 focus:ring-violet-500">
            <button type="submit" class="px-6 py-3 bg-violet-600 hover:bg-violet-500 text-white rounded-xl font-semibold text-sm">Search</button>
        </form>
        @if(strlen($query) > 0 && strlen($query) < 2)
            <p class="mt-3 text-sm text-amber-400/90">Enter at least 2 characters.</p>
        @endif
    </div>

    @if(strlen($query) >= 2)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach([
                'Restaurants' => $restaurants,
                'Users' => $users,
                'Waiters' => $waiters,
                'Orders' => $orders,
            ] as $title => $items)
                <div class="glass-card rounded-2xl border border-white/10 overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/5">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">{{ $title }} <span class="text-white/40">({{ $items->count() }})</span></h2>
                    </div>
                    <ul class="divide-y divide-white/5">
                        @forelse($items as $item)
                            <li class="px-6 py-4 hover:bg-white/5">
                                @if($title === 'Restaurants')
                                    <a href="{{ route('admin.restaurants.show', $item) }}" class="text-white font-semibold hover:text-violet-300">{{ $item->name }}</a>
                                    <p class="text-xs text-white/40 mt-1">{{ $item->location }}</p>
                                @elseif($title === 'Users' || $title === 'Waiters')
                                    <a href="{{ route('admin.users.show', $item) }}" class="text-white font-semibold hover:text-violet-300">{{ $item->name }}</a>
                                    <p class="text-xs text-white/40 mt-1">{{ $item->email }} · {{ $item->restaurant?->name ?? '—' }}</p>
                                @else
                                    <a href="{{ route('admin.orders.show', $item) }}" class="text-white font-semibold hover:text-violet-300">Order #{{ $item->id }}</a>
                                    <p class="text-xs text-white/40 mt-1">{{ $item->restaurant?->name }} · {{ ucfirst($item->status) }} · {{ $currencySymbol }} {{ number_format($item->total_amount) }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-white/40 text-sm">No matches</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    @elseif(strlen($query) === 0)
        <p class="text-white/40 text-sm text-center py-12">Search across all venues, users, and orders from one place.</p>
    @endif
</x-admin-layout>
