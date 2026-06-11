<x-manager-layout>
    <x-slot name="header">
        Food Ratings
    </x-slot>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Food Ratings</h2>
            <p class="text-sm font-medium text-white/40 uppercase tracking-wider">What customers ordered and how they rated it</p>
        </div>
        <a href="{{ route('manager.feedback.index') }}" class="text-[11px] font-bold text-violet-400 hover:text-violet-300 uppercase tracking-wider">← Service & Waiter Feedback</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass-card p-6 rounded-3xl">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1">Average Food Rating</p>
            <h3 class="text-4xl font-black text-white">{{ number_format($avgRating, 1) }}/5</h3>
        </div>
        <div class="glass-card p-6 rounded-3xl">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1">Total Food Reviews</p>
            <h3 class="text-4xl font-black text-white">{{ number_format($totalReviews) }}</h3>
        </div>
        <div class="glass-card p-6 rounded-3xl">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1">5-Star Reviews</p>
            <h3 class="text-4xl font-black text-white">{{ $ratingBreakdown[5] ?? 0 }}</h3>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($feedbacks as $feedback)
            <div class="glass-card p-6 rounded-3xl">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <h5 class="font-bold text-white">{{ $feedback->order?->customer_name ?? 'Customer' }}</h5>
                        <p class="text-[10px] font-bold text-white/30 uppercase tracking-widest">
                            {{ $feedback->created_at->diffForHumans() }}
                            @if($feedback->order?->table_number)
                                • Table #{{ $feedback->order->table_number }}
                            @endif
                            • Order #{{ $feedback->order_id }}
                        </p>
                    </div>
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= $feedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" class="{{ $i <= $feedback->rating ? 'text-amber-400' : 'text-white/10' }}">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        @endfor
                    </div>
                </div>

                @if($feedback->order?->items?->isNotEmpty())
                    <div class="mb-4 rounded-2xl bg-white/5 border border-white/10 p-4">
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-2">Ordered items</p>
                        <ul class="space-y-1.5">
                            @foreach($feedback->order->items as $item)
                                <li class="text-sm text-white/80 flex justify-between gap-3">
                                    <span>{{ $item->name }}</span>
                                    <span class="text-white/40 shrink-0">×{{ $item->quantity }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($feedback->comment)
                    <p class="text-sm text-white/70 leading-relaxed italic">"{{ $feedback->comment }}"</p>
                @endif
            </div>
        @empty
            <div class="glass-card p-16 rounded-3xl text-center">
                <h3 class="text-xl font-bold text-white mb-2">No food ratings yet</h3>
                <p class="text-white/40">When customers rate food via WhatsApp, reviews will appear here with their order items.</p>
            </div>
        @endforelse

        <div class="mt-6">{{ $feedbacks->links() }}</div>
    </div>
</x-manager-layout>
