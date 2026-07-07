{{-- Auto-polls every 5 s so new cashier orders appear without a manual refresh --}}
<div class="min-h-screen flex bg-slate-100" wire:poll.5s>

    {{-- ── Main ────────────────────────────────────────────────────── --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar: category pills + live clock --}}
        <div class="flex items-center justify-between px-6 py-3 border-b bg-white shrink-0">
            <div class="flex gap-2 flex-wrap">
                <button wire:click="selectCategory(null)"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition
                               {{ is_null($activeCategoryId) ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All
                </button>
                @foreach ($categories as $cat)
                    <button wire:click="selectCategory({{ $cat->CategoryID }})"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition
                                   {{ $activeCategoryId === $cat->CategoryID ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $cat->CategoryName }}
                    </button>
                @endforeach
            </div>

            {{-- Live clock via Alpine --}}
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

        {{-- ── Order cards grid ──────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto p-6">
            @if ($displayed->isEmpty())
                <p class="text-center text-slate-400 mt-24 text-base">No pending orders right now.</p>
            @else
                <div class="grid grid-cols-3 gap-4">
                    @foreach ($displayed as $order)
                        @php
                            $isOverflow = $loop->index >= 5;
                            $allReady   = $order->items->every(fn ($i) => $i->ItemStatus === 'R'); // R = ready
                        @endphp

                        {{-- Card wrapper --}}
                        <div class="relative rounded-lg border border-slate-200 bg-white shadow-sm flex flex-col
                                    {{ $isOverflow ? 'opacity-50 pointer-events-none select-none' : 'cursor-pointer hover:shadow-md transition-shadow' }}"
                             @if (! $isOverflow) wire:click="openOrder({{ $order->OrderID }})" @endif>

                            {{-- Overflow dimmed overlay with +N counter --}}
                            @if ($isOverflow)
                                <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/50 z-10">
                                    <span class="text-5xl font-bold text-slate-700">+{{ $overflow }}</span>
                                </div>
                            @endif

                            {{-- Card header --}}
                            <div class="flex justify-between items-center px-3 py-2 border-b bg-slate-50 rounded-t-lg shrink-0">
                                <span class="font-semibold text-sm text-slate-700">#{{ $order->OrderID }}</span>
                                <span class="text-xs text-slate-500">{{ $order->order_type_label }}</span>
                            </div>

                            {{-- Scrollable item list (display-only in card; interact via popup) --}}
                            <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5 max-h-44">
                                @foreach ($order->items as $item)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="text-slate-700 truncate">{{ $item->dish->DishName }}</span>
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold text-white shrink-0
                                            @if ($item->ItemStatus === 'S') bg-amber-500
                                            @elseif ($item->ItemStatus === 'P') bg-blue-500
                                            @else bg-green-600 @endif">
                                            {{ $item->ItemStatus }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Complete button (wire:click.stop so it doesn't also open the popup) --}}
                            <div class="px-3 py-2.5 border-t shrink-0">
                                <button
                                    @if (! $isOverflow) wire:click.stop="completeOrder({{ $order->OrderID }})" @endif
                                    @disabled(! $allReady)
                                    class="w-full py-1.5 rounded text-sm font-medium text-white transition
                                           {{ $allReady ? 'bg-slate-700 hover:bg-slate-800' : 'bg-slate-300 cursor-not-allowed' }}">
                                    Complete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    {{-- ── Order detail popup ──────────────────────────────────────── --}}
    @if ($activeOrder)
        @php $popupAllReady = $activeOrder->items->every(fn ($i) => $i->ItemStatus === 'R'); @endphp

        {{-- Backdrop: click outside the card to close --}}
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4"
             wire:click.self="closeOrder">

            <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col max-h-[85vh]">

                {{-- Popup header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b shrink-0">
                    <div>
                        <span class="font-semibold text-base text-slate-800">Order #{{ $activeOrder->OrderID }}</span>
                        <span class="ml-2 text-xs text-slate-500 bg-slate-100 rounded-full px-2 py-0.5">
                            {{ $activeOrder->order_type_label }}
                        </span>
                    </div>
                    <button wire:click="closeOrder"
                            class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                </div>

                {{-- Scrollable item list with interactive status badges --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    @foreach ($activeOrder->items as $item)
                        @php $done = $item->ItemStatus === 'R'; @endphp

                        <div class="flex items-start gap-3">
                            {{-- Item details --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-slate-800">{{ $item->dish->DishName }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Qty: {{ $item->Quantity }}
                                    @if ($item->Choice)
                                        &middot; {{ $item->Choice }}
                                    @endif
                                </p>
                                @if ($item->SpecialInstruction)
                                    <p class="text-xs text-slate-400 italic mt-0.5">&ldquo;{{ $item->SpecialInstruction }}&rdquo;</p>
                                @endif
                            </div>

                            {{-- Status badge: tappable if S or P; locked green when R --}}
                            @if (! $done)
                                <button
                                    wire:click="advanceStatus({{ $item->OrderItemID }})"
                                    wire:loading.attr="disabled"
                                    wire:target="advanceStatus({{ $item->OrderItemID }})"
                                    class="w-9 h-9 flex items-center justify-center rounded-full text-xs font-bold text-white shrink-0 transition
                                           @if ($item->ItemStatus === 'S')
                                               bg-amber-500 hover:bg-blue-500
                                           @else
                                               bg-blue-500 hover:bg-green-600
                                           @endif
                                           wire:loading:class='opacity-50 cursor-wait'"
                                    title="{{ $item->ItemStatus === 'S' ? 'Tap to start preparing' : 'Tap to mark ready' }}"
                                >
                                    {{ $item->ItemStatus }}
                                </button>
                            @else
                                <span class="w-9 h-9 flex items-center justify-center rounded-full text-xs font-bold text-white bg-green-600 shrink-0">
                                    R
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Popup Complete button --}}
                <div class="px-5 py-4 border-t shrink-0">
                    <button
                        wire:click="completeOrder({{ $activeOrder->OrderID }})"
                        @disabled(! $popupAllReady)
                        class="w-full py-2.5 rounded font-medium text-white transition
                               {{ $popupAllReady ? 'bg-slate-700 hover:bg-slate-800' : 'bg-slate-300 cursor-not-allowed' }}">
                        Complete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>