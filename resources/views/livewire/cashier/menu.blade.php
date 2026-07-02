<div class="min-h-screen flex bg-white" x-data>
    {{-- Sidebar --}}
    <aside class="w-48 bg-slate-700 text-white flex flex-col py-6">
        <h1 class="px-6 text-xl font-semibold mb-6">Menu</h1>

        <button
            wire:click="selectCategory(null)"
            class="text-left px-6 py-2 hover:bg-slate-600 {{ is_null($this->activeCategoryId) ? 'bg-slate-600' : '' }}"
        >
            All
        </button>

        @foreach ($categories as $category)
            <button
                wire:click="selectCategory({{ $category->CategoryID }})"
                class="text-left px-6 py-2 hover:bg-slate-600 {{ $this->activeCategoryId === $category->CategoryID ? 'bg-slate-600' : '' }}"
            >
                {{ $category->CategoryName }}
            </button>
        @endforeach

        <div class="mt-auto px-6 pt-6 flex flex-col gap-2">
            <button wire:click="changeOrderType" class="text-sm text-slate-300 hover:text-white text-left">
                Change Order Type
            </button>
            <button wire:click="signOut" class="text-sm text-slate-300 hover:text-white text-left">
                Sign Out
            </button>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="flex-1 p-6 relative">
        <div class="grid grid-cols-3 gap-4">
            @foreach ($dishes as $dish)
                <div class="border rounded-lg overflow-hidden {{ ! $dish->Availability ? 'opacity-40 pointer-events-none' : 'cursor-pointer hover:shadow-md transition' }}"
                     @if($dish->Availability)
                        onclick="window.location.href='{{ route('cashier.dish', $dish) }}'"
                     @endif
                >
                    <div class="bg-slate-200 h-28 flex items-center justify-center text-slate-400 text-3xl">
                        &#128247;
                    </div>
                    <div class="p-3">
                        <div class="flex justify-between items-start">
                            <p class="font-semibold text-sm">{{ $dish->DishName }}</p>
                            <span class="text-xs text-slate-400">{{ $dish->DishCode }}</span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $dish->Description }}</p>
                        <p class="font-semibold mt-2">${{ number_format($dish->Price, 2) }}</p>
                        @unless($dish->Availability)
                            <p class="text-xs text-red-500 mt-1">Currently unavailable</p>
                        @endunless
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Floating cart button --}}
        <button
            wire:click="goToCart"
            class="fixed bottom-8 right-8 bg-slate-700 hover:bg-slate-800 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg"
        >
            &#128722;
            @if ($cartCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif
        </button>
    </main>
</div>