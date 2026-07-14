<x-manager-layout>
    <x-slot name="header">
        Live Orders
    </x-slot>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Live Orders</h2>
            <p class="text-sm font-medium text-white/40 uppercase tracking-wider">Real-time order management</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openCreateOrderModal()" class="bg-violet-600 hover:bg-violet-700 text-white px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 shadow-lg shadow-violet-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Create Order
            </button>
            <div class="flex items-center gap-2 glass px-4 py-2.5 rounded-xl">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Live Sync Active</span>
            </div>
            <button onclick="window.location.reload()" class="glass px-5 py-3 rounded-xl font-semibold text-white/60 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Live Kanban Board: Received → … → Completed -->
    @php
        $columns = [
            ['key' => 'received', 'orders' => $receivedOrders, 'dot' => 'bg-rose-500', 'badge' => 'bg-rose-500/20 text-rose-500 border-rose-500/20', 'tag' => 'bg-rose-500/20 text-rose-500 border-rose-500/20', 'empty' => 'No received orders'],
            ['key' => 'accepted', 'orders' => $acceptedOrders, 'dot' => 'bg-violet-500', 'badge' => 'bg-violet-500/20 text-violet-400 border-violet-500/20', 'tag' => 'bg-violet-500/20 text-violet-400 border-violet-500/20', 'empty' => 'No accepted orders'],
            ['key' => 'preparing', 'orders' => $preparingOrders, 'dot' => 'bg-amber-500', 'badge' => 'bg-amber-500/20 text-amber-500 border-amber-500/20', 'tag' => 'bg-amber-500/20 text-amber-500 border-amber-500/20', 'empty' => 'No preparing orders'],
            ['key' => 'ready', 'orders' => $readyOrders, 'dot' => 'bg-cyan-500', 'badge' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/20', 'tag' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/20', 'empty' => 'No ready orders'],
            ['key' => 'served', 'orders' => $servedOrders, 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-500/20 text-emerald-500 border-emerald-500/20', 'tag' => 'bg-emerald-500/20 text-emerald-500 border-emerald-500/20', 'empty' => 'No served orders'],
            ['key' => 'completed', 'orders' => $completedOrders, 'dot' => 'bg-slate-400', 'badge' => 'bg-slate-500/20 text-slate-300 border-slate-500/20', 'tag' => 'bg-slate-500/20 text-slate-300 border-slate-500/20', 'empty' => 'No completed orders today'],
        ];
        $nextMap = [
            'received' => ['status' => 'accepted', 'title' => 'Accept'],
            'accepted' => ['status' => 'preparing', 'title' => 'Start preparing'],
            'preparing' => ['status' => 'ready', 'title' => 'Mark ready'],
            'ready' => ['status' => 'served', 'title' => 'Mark served'],
            'served' => null,
            'completed' => null,
        ];
    @endphp

    <div class="flex gap-4 overflow-x-auto pb-2 -mx-1 px-1 snap-x">
        @foreach($columns as $col)
            @php
                $meta = $workflowMeta[$col['key']] ?? ['label' => ucfirst($col['key'])];
                $next = $nextMap[$col['key']] ?? null;
            @endphp
            <div class="glass-card p-4 rounded-2xl min-h-[500px] min-w-[280px] w-[280px] shrink-0 snap-start {{ $col['key'] === 'completed' ? 'opacity-80' : '' }}">
                <div class="flex items-center justify-between mb-5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 {{ $col['dot'] }} rounded-full {{ in_array($col['key'], ['received','preparing','ready'], true) ? 'animate-pulse' : '' }}"></div>
                        <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">{{ $meta['label'] }}</h4>
                    </div>
                    <span class="{{ $col['badge'] }} text-[11px] font-bold px-2.5 py-1 rounded-full border">{{ $col['orders']->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($col['orders'] as $order)
                        @php
                            $stageStarted = $order->{$col['key'].'_at'} ?? $order->updated_at ?? $order->created_at;
                            $minsInStage = $stageStarted ? $stageStarted->diffInMinutes(now()) : 0;
                            $isWhatsAppOrder = filled($order->whatsapp_jid);
                            $billAlreadySent = ! is_null($order->bill_image_pushed_at);
                        @endphp
                        <div class="glass p-4 rounded-xl card-hover group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex flex-col gap-1">
                                    <span class="{{ $col['tag'] }} px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border">Table #{{ $order->table_number }}</span>
                                    @if($order->waiter)
                                        <span class="text-[10px] font-medium text-cyan-500">{{ $order->waiter->name }}</span>
                                    @else
                                        <span class="text-[10px] font-medium text-white/30">Unassigned</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-medium text-white/40 block">{{ $order->created_at->diffForHumans() }}</span>
                                    @if($col['key'] !== 'completed')
                                        <span class="text-[10px] font-semibold text-amber-400/90">{{ $minsInStage }}m in stage</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-1.5 mb-4">
                                @foreach($order->items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="font-semibold text-white">{{ $item->quantity }}x {{ $item->name ?? ($item->menuItem ? $item->menuItem->name : 'Custom Order') }}</span>
                                        <span class="text-white/40">{{ $currencySymbol }} {{ number_format($item->total) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between pt-3 border-t border-white/5 gap-2 flex-wrap">
                                <span class="font-bold text-white text-sm">{{ $currencySymbol }} {{ number_format($order->total_amount) }}</span>
                                <div class="flex gap-1.5 flex-wrap justify-end">
                                    @if($col['key'] === 'completed')
                                        <span class="text-[10px] font-medium text-emerald-400 inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            Done
                                        </span>
                                    @elseif($col['key'] === 'served')
                                        @if($isWhatsAppOrder && ! $billAlreadySent)
                                            <form action="{{ route('manager.orders.whatsapp-bill', $order) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold">Confirm order</button>
                                            </form>
                                        @endif
                                        @if(! $isWhatsAppOrder || $billAlreadySent)
                                            <button onclick="openPaymentModal({{ $order->id }}, {{ $order->total_amount }})" class="bg-fin-primary text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold">Pay</button>
                                            <form action="{{ route('manager.orders.update', $order) }}" method="POST" class="inline" onsubmit="return confirm('Mark this order completed?');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="bg-emerald-600 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold">Complete</button>
                                            </form>
                                        @endif
                                    @elseif($next)
                                        <form action="{{ route('manager.orders.update', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="{{ $next['status'] }}">
                                            <button type="submit" class="bg-linear-to-r from-fin-primary to-fin-primary-dark text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold" title="{{ $next['title'] }}">
                                                {{ $next['title'] }}
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="openEditOrderModal({{ $order->id }}, '{{ $order->table_number }}', '{{ $order->customer_phone }}', '{{ $order->customer_name }}')" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40 hover:text-fin-primary" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <form action="{{ route('manager.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Delete this order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40 hover:text-rose-500" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/30 text-center py-8">{{ $col['empty'] }}</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Order Modal -->
    <div id="createOrderModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6">
        <div class="bg-surface-900 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-white/10 max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white tracking-tight">Create New Order</h3>
                <button onclick="closeCreateOrderModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all text-white/40 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('manager.orders.store') }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Table</label>
                        <select name="table_number" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all [&>option]:text-black">
                            <option value="">Select Table</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->name }}">{{ $table->name }} ({{ $table->capacity }} pax)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer Name (Optional)</label>
                        <input type="text" name="customer_name" placeholder="Guest Name" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer WhatsApp / phone</label>
                    <input type="text" name="customer_phone" placeholder="2557XXXXXXXX (recommended)" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    <p class="mt-1.5 text-[10px] text-white/35 leading-relaxed">After the order is <strong class="text-white/50">Served</strong>, use <strong class="text-white/50">Confirm order</strong> on the board to send the bill image to this number (nothing is sent until you confirm).</p>
                </div>

                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Menu Items</label>
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($menuItems as $item)
                            <div class="flex items-center justify-between glass p-3 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" id="item_{{ $item->id }}" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}" class="w-5 h-5 rounded border-white/20 bg-white/5 text-violet-600 focus:ring-fin-primary focus:ring-offset-0" onchange="toggleQuantity({{ $loop->index }})">
                                    <label for="item_{{ $item->id }}" class="text-sm font-medium text-white cursor-pointer select-none">
                                        {{ $item->name }}
                                        <span class="block text-[10px] text-white/40">{{ $currencySymbol }} {{ number_format($item->price) }}</span>
                                    </label>
                                </div>
                                <div class="flex items-center gap-2 opacity-50 pointer-events-none transition-all" id="qty_container_{{ $loop->index }}">
                                    <button type="button" onclick="adjustQty({{ $loop->index }}, -1)" class="w-8 h-8 rounded-lg bg-fin-mist flex items-center justify-center text-white hover:bg-white/20">-</button>
                                    <input type="number" name="items[{{ $loop->index }}][quantity]" id="qty_{{ $loop->index }}" value="1" min="1" class="w-12 text-center bg-transparent border-none text-white font-bold focus:ring-0 p-0" readonly>
                                    <button type="button" onclick="adjustQty({{ $loop->index }}, 1)" class="w-8 h-8 rounded-lg bg-fin-mist flex items-center justify-center text-white hover:bg-white/20">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-white/10">
                    <button type="submit" class="w-full bg-linear-to-r from-fin-primary to-fin-primary-dark text-white py-3.5 rounded-xl font-bold hover:shadow-lg hover:shadow-fin-primary/25 transition-all">
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6">
        <div class="bg-surface-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-white/10">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white tracking-tight">Edit Order</h3>
                <button onclick="closeEditOrderModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all text-white/40 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form id="editOrderForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Table</label>
                    <select name="table_number" id="edit_table_number" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-fin-primary transition-all [&>option]:text-black">
                        @foreach($tables as $table)
                            <option value="{{ $table->name }}">{{ $table->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer Name</label>
                    <input type="text" name="customer_name" id="edit_customer_name" placeholder="Guest Name" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer WhatsApp / phone</label>
                    <input type="text" name="customer_phone" id="edit_customer_phone" placeholder="2557XXXXXXXX" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary transition-all">
                    <p class="mt-1.5 text-[10px] text-white/35">Used when you tap <strong class="text-white/60">Confirm order</strong> on a Served order to WhatsApp the bill image.</p>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-violet-600 text-white py-3.5 rounded-xl font-bold hover:bg-violet-700 transition-all">
                        Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6">
        <div class="bg-surface-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-white/10">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white tracking-tight">Process Payment</h3>
                        <p class="text-sm font-medium text-white/40">{{ config('tiptap.payment_gateway') }} payment</p>
                    </div>
                    <button onclick="closePaymentModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all text-white/40 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="glass p-5 rounded-xl mb-6 flex justify-between items-center">
                    <span class="font-medium text-white/60">Total Amount</span>
                    <span id="modalAmount" class="text-2xl font-bold text-white">{{ $currencySymbol }} 0</span>
                </div>

                <form id="selcomPayForm" class="space-y-4">
                    <input type="hidden" id="modalOrderId">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer Phone (07XXXXXXXX)</label>
                        <input type="text" id="customerPhone" required placeholder="e.g. 0744963858" 
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Customer Name</label>
                        <input type="text" id="customerName" required placeholder="e.g. John Doe" 
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    </div>
                    <button type="submit" id="payButton" class="w-full bg-linear-to-r from-fin-primary to-fin-primary-dark text-white py-3.5 rounded-xl font-semibold hover:shadow-lg hover:shadow-fin-primary/25 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>
                        </svg>
                        Send USSD Push
                    </button>
                </form>

                <div id="pollingStatus" class="hidden mt-6 p-5 bg-cyan-500/10 rounded-xl border border-cyan-500/20 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-8 h-8 border-3 border-cyan-400 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-sm font-semibold text-cyan-600">Waiting for customer to enter PIN...</p>
                        <p class="text-[10px] text-white/40 font-medium uppercase tracking-wider">Do not close this window</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pollingInterval = null;

        function openPaymentModal(orderId, amount) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('modalAmount').innerText = @json($currencySymbol) + ' ' + new Intl.NumberFormat().format(amount);
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('paymentModal').classList.add('flex');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            document.getElementById('paymentModal').classList.remove('flex');
            if (pollingInterval) clearInterval(pollingInterval);
            document.getElementById('selcomPayForm').classList.remove('hidden');
            document.getElementById('pollingStatus').classList.add('hidden');
        }

        function openCreateOrderModal() {
            document.getElementById('createOrderModal').classList.remove('hidden');
            document.getElementById('createOrderModal').classList.add('flex');
        }

        function closeCreateOrderModal() {
            document.getElementById('createOrderModal').classList.add('hidden');
            document.getElementById('createOrderModal').classList.remove('flex');
        }

        function openEditOrderModal(orderId, tableNumber, phone, name) {
            const form = document.getElementById('editOrderForm');
            form.action = `/manager/orders/${orderId}`;
            document.getElementById('edit_table_number').value = tableNumber;
            document.getElementById('edit_customer_phone').value = phone;
            document.getElementById('edit_customer_name').value = name;
            
            document.getElementById('editOrderModal').classList.remove('hidden');
            document.getElementById('editOrderModal').classList.add('flex');
        }

        function closeEditOrderModal() {
            document.getElementById('editOrderModal').classList.add('hidden');
            document.getElementById('editOrderModal').classList.remove('flex');
        }

        function toggleQuantity(index) {
            const checkbox = document.querySelector(`input[name="items[${index}][id]"]`);
            const container = document.getElementById(`qty_container_${index}`);
            
            if (checkbox.checked) {
                container.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                container.classList.add('opacity-50', 'pointer-events-none');
            }
        }

        function adjustQty(index, change) {
            const input = document.getElementById(`qty_${index}`);
            let val = parseInt(input.value) + change;
            if (val < 1) val = 1;
            input.value = val;
        }

        document.getElementById('selcomPayForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payButton = document.getElementById('payButton');
            const orderId = document.getElementById('modalOrderId').value;
            const phone = document.getElementById('customerPhone').value;
            const name = document.getElementById('customerName').value;

            payButton.disabled = true;
            payButton.innerHTML = '<svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';

            try {
                const response = await fetch('{{ route("manager.payments.selcom.initiate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        phone: phone,
                        name: name,
                        email: 'customer@tiptap.com'
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById('selcomPayForm').classList.add('hidden');
                    document.getElementById('pollingStatus').classList.remove('hidden');
                    startPolling(orderId);
                } else {
                    alert('Error: ' + (result.message || 'Failed to initiate payment'));
                    payButton.disabled = false;
                    payButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg> Send USSD Push';
                }
            } catch (error) {
                alert('Connection error. Please try again.');
                payButton.disabled = false;
                payButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg> Send USSD Push';
            }
        });

        function startPolling(orderId) {
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/manager/payments/selcom/status/${orderId}`);
                    const result = await response.json();

                    if (result.status === 'paid') {
                        clearInterval(pollingInterval);
                        alert('Payment Successful!');
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 5000);
        }
    </script>
</x-manager-layout>
