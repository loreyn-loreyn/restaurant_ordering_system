{{-- Auto-polls every 5s to stay in sync with kitchen --}}

<div class="min-h-screen bg-slate-100 flex flex-col" wire:poll.5s>

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b bg-white shrink-0">
        <h1 class="text-2xl font-semibold text-slate-800">Customer Order Status Display</h1>

        {{-- Live clock, matches kitchen views --}}
        <div x-data="{
                t: '',
                init () {
                    const tick = () => {
                        const d = new Date();
                        this.t = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
                    };
                    tick();
                    setInterval(tick, 1000);
                }
             }"
             x-text="t"
             class="border border-slate-300 rounded px-3 py-1 text-sm font-medium text-slate-700 tabular-nums shrink-0">
        </div>
    </div>

    <div class="flex-1 flex flex-col gap-4 p-6 overflow-hidden">

        {{-- Pending Orders --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 shrink-0">
            <h2 class="font-semibold text-base mb-3 text-slate-700">Pending Orders</h2>
            @if ($serving->where(fn ($o) => $o->items->every(fn ($i) => $i->ItemStatus !== 'R') === false && $o->items->contains('ItemStatus', 'S'))->isEmpty() && $serving->isEmpty())
                <p class="text-slate-400 text-sm">No pending orders.</p>
            @else
                @php
                    $pending = $serving->filter(fn ($o) =>
                        $o->items->contains(fn ($i) => $i->ItemStatus === 'S')
                    );
                @endphp
                @if ($pending->isEmpty())
                    <p class="text-slate-400 text-sm">No pending orders.</p>
                @else
                    <div class="grid grid-cols-4 gap-x-8 gap-y-1">
                        @foreach ($pending as $order)
                            <span class="text-slate-500 text-sm">Order {{ $order->OrderID }}</span>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        {{-- Serving --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 flex-1 overflow-hidden flex flex-col">
            <h2 class="font-semibold text-base mb-3 text-slate-700">Serving</h2>
            @if ($serving->isEmpty())
                <p class="text-slate-400 text-sm">No orders being served right now.</p>
            @else
                <div class="flex gap-4 overflow-x-auto pb-2">
                    @foreach ($serving as $order)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg shadow-sm p-4 shrink-0 w-52 flex flex-col gap-1">
                            <p class="font-semibold text-sm text-slate-800 mb-1">Order {{ $order->OrderID }}</p>
                            <p class="text-xs text-slate-500 font-medium mb-1">Orders:</p>
                            @foreach ($order->items as $item)
                                <p class="text-xs text-slate-600">
                                    {{ $item->dish->DishName }} –
                                    @if ($item->ItemStatus === 'S')
                                        <span class="text-amber-500 font-medium">Start</span>
                                    @elseif ($item->ItemStatus === 'P')
                                        <span class="text-blue-500 font-medium">Preparing</span>
                                    @else
                                        <span class="text-green-600 font-medium">Ready</span>
                                    @endif
                                </p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Completed --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 shrink-0">
            <h2 class="font-semibold text-base mb-3 text-slate-700">Completed</h2>
            @if ($completed->isEmpty())
                <p class="text-slate-400 text-sm">No completed orders yet.</p>
            @else
                <div class="grid grid-cols-4 gap-x-8 gap-y-1">
                    @foreach ($completed as $order)
                        <span class="text-slate-500 text-sm">Order {{ $order->OrderID }}</span>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>