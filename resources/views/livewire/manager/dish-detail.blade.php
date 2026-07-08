<div class="flex flex-col h-full">

    {{-- ── Top bar ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-6 py-3 border-b bg-white shrink-0">
        <a href="{{ route('manager.dishes') }}" wire:navigate class="text-slate-400 hover:text-slate-600 text-lg">&larr;</a>
        <div>
            <span class="text-lg font-semibold text-slate-800">{{ $dish->DishName }}</span>
            <span class="text-xs text-slate-400 ml-2">{{ $dish->DishCode }}</span>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-2xl grid grid-cols-2 gap-6">

            {{-- Photo --}}
            <div class="col-span-2 rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-slate-200 h-56 flex items-center justify-center text-slate-400 text-4xl overflow-hidden">
                    @if ($dish->PhotoUrl)
                        <img src="{{ $dish->PhotoUrl }}" alt="{{ $dish->DishName }}" class="w-full h-full object-cover">
                    @else
                        &#128247;
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="col-span-2 rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Dish Information</p>
                <div class="grid grid-cols-2 gap-y-2.5 text-sm">
                    <span class="text-slate-400">Category</span>
                    <span class="text-slate-700">{{ $dish->category->CategoryName ?? 'Uncategorized' }}</span>

                    <span class="text-slate-400">Price</span>
                    <span class="text-slate-700">₱{{ number_format($dish->Price, 2) }}</span>

                    <span class="text-slate-400">Dish Code</span>
                    <span class="text-slate-700">{{ $dish->DishCode }}</span>

                    <span class="text-slate-400">Availability</span>
                    <span class="{{ $dish->Availability ? 'text-green-600' : 'text-red-500' }} font-medium">
                        {{ $dish->Availability ? 'Available' : 'Unavailable' }}
                    </span>
                </div>

                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-4 mb-1">Description</p>
                <p class="text-sm text-slate-700">{{ $dish->Description }}</p>
            </div>
        </div>

        <p class="text-xs text-slate-400 mt-6 max-w-2xl">
            To edit or delete this dish, go back to the dish list and long-press its card.
        </p>
    </div>
</div>