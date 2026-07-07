<div class="min-h-screen bg-white flex flex-col">
    <div class="flex items-center px-6 py-4 border-b shrink-0">
        <a href="{{ $editingCartKey ? route('cashier.cart') : route('cashier.dishes') }}" wire:navigate class="text-slate-600 text-base">&#8592; Back</a>
        @if ($editingCartKey)
            <span class="ml-4 text-sm text-slate-400">Customizing item in your cart</span>
        @endif
    </div>

    {{-- Scrollable content area, padded at the bottom so the fixed footer never covers content --}}
    <div class="flex-1 overflow-y-auto pb-32">
        <div class="bg-slate-500 h-56 flex items-center justify-center text-white text-5xl overflow-hidden">
            @if ($dish->PhotoUrl)
                <img src="{{ $dish->PhotoUrl }}" alt="{{ $dish->DishName }}" class="w-full h-full object-cover">
            @else
                &#128247;
            @endif
        </div>

        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-3xl font-semibold">{{ $dish->DishName }}</h1>
            <p class="text-xl font-medium text-slate-700 mb-2">${{ number_format($dish->Price, 2) }}</p>
            <p class="text-base text-slate-500 mb-6">{{ $dish->Description }}</p>

            @if (! empty($choices))
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Choice</h2>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($choices as $option)
                            <label class="flex items-center gap-2 text-base">
                                <input type="radio" wire:model="choice" value="{{ $option }}" class="w-4 h-4">
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2">Special Instructions</h2>
                <textarea
                    wire:model="specialInstruction"
                    rows="4"
                    placeholder="e.g. no mayo"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-slate-400"
                ></textarea>
            </div>
        </div>
    </div>

    {{-- Fixed footer: Proceed to Payment, Add to Cart, quantity stepper --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-6">
        <div class="max-w-2xl mx-auto w-full flex items-center justify-between gap-4 flex-wrap">
            <div class="flex gap-3">
                <button wire:click="proceedToPayment" class="bg-slate-600 hover:bg-slate-700 text-white px-5 py-3 rounded text-base">
                    Proceed to Payment
                </button>
                <button wire:click="addToCart" class="bg-slate-600 hover:bg-slate-700 text-white px-5 py-3 rounded text-base">
                    {{ $editingCartKey ? 'Update Item' : 'Add to Cart' }}
                </button>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="decrement" class="w-10 h-10 rounded border border-slate-300 text-slate-600 text-lg">-</button>
                <span class="w-8 text-center text-lg">{{ $quantity }}</span>
                <button wire:click="increment" class="w-10 h-10 rounded border border-slate-300 text-slate-600 text-lg">+</button>
            </div>
        </div>
    </div>
</div>