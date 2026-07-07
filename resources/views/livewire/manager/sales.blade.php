<div class="flex flex-col h-full">

    {{-- ── Top bar: period pills ──────────────────────────────────── --}}
    <div class="flex items-center px-6 py-3 border-b bg-white shrink-0">
        <div class="flex gap-2 flex-wrap">
            @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition
                               {{ $period === $key ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6 space-y-6">

        {{-- ── KPI cards ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs text-slate-500">Total Sales</p>
                <p class="text-xl font-semibold text-slate-800 mt-1">₱{{ number_format($totalSales, 2) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs text-slate-500">Orders</p>
                <p class="text-xl font-semibold text-slate-800 mt-1">{{ number_format($orderCount) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs text-slate-500">Average Check</p>
                <p class="text-xl font-semibold text-slate-800 mt-1">₱{{ number_format($averageCheck, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">

            {{-- ── Sales bar chart ─────────────────────────────────── --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-sm font-semibold text-slate-700 mb-4">Sales by {{ ucfirst($period) }}</p>
                <div class="flex items-end gap-2 h-40">
                    @foreach ($buckets as $label => $value)
                        <div class="flex-1 flex flex-col items-center justify-end h-full">
                            <div class="w-full bg-slate-700 rounded-t"
                                 style="height: {{ $maxBucket > 0 ? max(2, ($value / $maxBucket) * 100) : 2 }}%"
                                 title="₱{{ number_format($value, 2) }}"></div>
                            <span class="text-[10px] text-slate-400 mt-1 truncate w-full text-center">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Sales by category donut ─────────────────────────── --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-sm font-semibold text-slate-700 mb-4">Sales Category</p>
                @php
                    $catTotal = array_sum($categorySales);
                    $palette = ['#334155', '#64748b', '#94a3b8', '#cbd5e1', '#0f172a', '#475569'];
                    $stops = [];
                    $acc = 0;
                    $i = 0;
                    foreach ($categorySales as $name => $amount) {
                        $pct = $catTotal > 0 ? ($amount / $catTotal) * 100 : 0;
                        $color = $palette[$i % count($palette)];
                        $stops[] = "$color " . $acc . "% " . ($acc + $pct) . "%";
                        $acc += $pct;
                        $i++;
                    }
                    $gradient = empty($stops) ? '#e2e8f0 0% 100%' : implode(', ', $stops);
                @endphp
                <div class="flex items-center gap-6">
                    <div class="w-28 h-28 rounded-full shrink-0 flex items-center justify-center"
                         style="background: conic-gradient({{ $gradient }})">
                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-xs font-semibold text-slate-700">
                            ₱{{ number_format($catTotal, 0) }}
                        </div>
                    </div>
                    <div class="space-y-1.5 text-xs">
                        @foreach ($categorySales as $name => $amount)
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $palette[$loop->index % count($palette)] }}"></span>
                                <span class="text-slate-600">{{ $name }}</span>
                                <span class="text-slate-400">₱{{ number_format($amount, 0) }}</span>
                            </div>
                        @endforeach
                        @if (empty($categorySales))
                            <span class="text-slate-400">No sales in this period.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">

            {{-- ── Top items ────────────────────────────────────────── --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-sm font-semibold text-slate-700 mb-3">Top Items</p>
                <div class="space-y-2">
                    @forelse ($topItems as $name => $qty)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ $name }}</span>
                            <span class="text-slate-400">{{ $qty }} sold</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No items sold in this period.</p>
                    @endforelse
                </div>
            </div>

            {{-- ── Discounts & Comps ───────────────────────────────── --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-sm font-semibold text-slate-700 mb-3">Discounts & Comps</p>
                <div class="space-y-2">
                    @forelse ($discountOrders as $order)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">
                                #{{ $order->OrderID }} &middot; {{ $order->discount->Reason }}
                                <span class="text-slate-400 text-xs block">{{ $order->OrderDate->format('M d, Y') }}</span>
                            </span>
                            <span class="text-slate-400 shrink-0">-₱{{ number_format($order->discount->Amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No discounts or comps in this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>