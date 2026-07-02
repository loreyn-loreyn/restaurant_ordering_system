<div class="min-h-screen bg-white relative flex flex-col">
    <div class="flex items-center px-6 py-4 border-b shrink-0">
        <button wire:click="goToMenu" class="text-slate-600 mr-3 text-lg">&#8592;</button>
        <h1 class="text-2xl font-semibold">Cart</h1>
    </div>

    {{-- Scrollable item list --}}
    <div class="flex-1 overflow-y-auto p-6 max-w-2xl mx-auto w-full pb-32 @if ($items->isEmpty()) flex items-center justify-center @endif">
        @forelse ($items as $item)
            <div
                wire:key="item-{{ $item['key'] }}"
                x-data="{
                    x: 0,
                    startX: 0,
                    dragging: false,
                    threshold: 90,
                    onStart(clientX) {
                        this.dragging = true;
                        this.startX = clientX - this.x;
                    },
                    onMove(clientX) {
                        if (! this.dragging) return;
                        let next = clientX - this.startX;
                        this.x = Math.min(0, Math.max(next, -140));
                    },
                    onEnd() {
                        if (! this.dragging) return;
                        this.dragging = false;
                        if (this.x < -this.threshold) {
                            this.x = -400;
                            $wire.removeItem('{{ $item['key'] }}');
                        } else {
                            this.x = 0;
                        }
                    }
                }"
                class="relative border-b overflow-hidden"
            >
                {{-- Red delete backdrop --}}
                <div class="absolute inset-0 bg-red-400 flex items-center justify-end pr-6 gap-2 text-white font-medium text-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        :class="x < -threshold ? 'scale-110' : 'scale-100'"
                        style="transition: transform 100ms ease-out"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6h16zM10 11v6M14 11v6" />
                    </svg>
                    <span>Remove</span>
                </div>

                {{-- Foreground card --}}
                <div
                    class="relative bg-white flex items-center justify-between py-3 px-1 select-none touch-pan-y"
                    :class="dragging ? '' : 'transition-transform duration-200 ease-out'"
                    :style="`transform: translateX(${x}px)`"
                    @pointerdown="onStart($event.clientX); $el.setPointerCapture($event.pointerId)"
                    @pointermove="onMove($event.clientX)"
                    @pointerup="onEnd()"
                    @pointercancel="onEnd()"
                >
                    <div class="flex items-center gap-3">
                        <div class="bg-slate-200 w-14 h-14 flex items-center justify-center rounded text-slate-400 text-xl">&#128247;</div>
                        <div>
                            <p class="font-medium text-base">{{ $item['dish_name'] }}</p>
                            <p class="text-sm text-slate-500">${{ number_format($item['price'], 2) }} &times; {{ $item['quantity'] }}</p>
                            @if ($item['choice'])
                                <p class="text-sm text-slate-400">{{ $item['choice'] }}</p>
                            @endif
                            @if ($item['special_instruction'])
                                <p class="text-xs text-slate-400 italic">{{ $item['special_instruction'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <button @pointerdown.stop wire:click="decrement('{{ $item['key'] }}')" class="w-7 h-7 rounded border border-slate-300 text-sm">-</button>
                            <span class="w-6 text-center text-base">{{ $item['quantity'] }}</span>
                            <button @pointerdown.stop wire:click="increment('{{ $item['key'] }}')" class="w-7 h-7 rounded border border-slate-300 text-sm">+</button>
                        </div>
                        <span class="text-base font-medium w-20 text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-slate-400 text-base text-center">Your cart is empty.</p>
        @endforelse

        @if ($items->isNotEmpty())
            <button wire:click="goToMenu" class="text-base text-slate-500 mt-3 hover:underline">+ Add more items</button>
        @endif
    </div>

    {{-- Fixed footer --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-6">
        <div class="max-w-2xl mx-auto w-full">
            <div class="flex justify-between items-center mb-4">
                <span class="font-semibold text-lg">Total</span>
                <span class="font-semibold text-lg">${{ number_format($this->subtotal, 2) }}</span>
            </div>

            <button
                wire:click="openDiscountModal"
                @disabled($items->isEmpty())
                class="w-full bg-slate-600 hover:bg-slate-700 disabled:bg-slate-300 text-white py-3 rounded font-medium text-base"
            >
                Proceed to Payment
            </button>
        </div>
    </div>

    {{-- Discounts & Comps modal --}}
    @if ($showDiscountModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-40">
            <div class="bg-white rounded-lg shadow-xl w-96 p-6 relative">
                <button wire:click="closeDiscountModal" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 text-xl leading-none" aria-label="Close">&times;</button>

                <h2 class="text-xl font-semibold mb-4">Discounts &amp; Comps</h2>

                <label class="block text-sm text-slate-600 mb-1">Type:</label>
                <select wire:model.live="discountType" class="w-full border rounded px-2 py-1 mb-4 text-sm">
                    <option value="None">None</option>
                    <option value="Discount">Discount</option>
                    <option value="Comp">Comp</option>
                </select>

                @if ($discountType !== 'None')
                    <label class="block text-sm text-slate-600 mb-1">Reason:</label>
                    <select wire:model.live="discountId" class="w-full border rounded px-2 py-1 mb-4 text-sm">
                        <option value="">None</option>
                        @foreach ($this->discountOptions as $option)
                            <option value="{{ $option->DiscountID }}">{{ $option->Reason }}</option>
                        @endforeach
                    </select>
                @endif

                <div class="text-sm space-y-1 mb-4">
                    <p>Amount to pay: <span class="font-medium">${{ number_format($this->subtotal, 2) }}</span></p>
                    <p>Discounted Amount: <span class="font-medium">${{ number_format($this->subtotal - $this->totalAfterDiscount, 2) }}</span></p>
                    <p>To pay after Discount: <span class="font-medium">${{ number_format($this->totalAfterDiscount, 2) }}</span></p>
                </div>

                <div class="flex gap-3">
                    <button disabled class="flex-1 bg-slate-300 text-white py-2 rounded text-sm cursor-not-allowed">Back</button>
                    @if ($discountType === 'None')
                        <button wire:click="skipDiscount" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white py-2 rounded text-sm">Proceed</button>
                    @else
                        <button wire:click="applyDiscount" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white py-2 rounded text-sm">Proceed</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Payment modal --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-96 p-6 relative">
                @if (! $paymentSuccessful)
                    <button wire:click="closePaymentModal" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 text-xl leading-none" aria-label="Close">&times;</button>

                    <h2 class="text-xl font-semibold mb-1 text-center">Payment</h2>
                    <p class="text-sm text-center mb-4">To Pay: ${{ number_format($this->totalAfterDiscount, 2) }}</p>

                    <label class="block text-sm text-slate-600 mb-1">Type:</label>
                    <select wire:model.live="paymentType" class="w-full border rounded px-2 py-1 mb-4 text-sm">
                        <option value="Cash">Cash</option>
                        <option value="Online/Card">Online/Card</option>
                    </select>

                    @if ($paymentType === 'Cash')
                        <label class="block text-sm text-slate-600 mb-1">Rendered Amount:</label>
                        <input type="text" readonly value="{{ $renderedAmount }}" placeholder="Enter Rendered Amount"
                               class="w-full border rounded px-2 py-1 mb-1 text-sm bg-slate-50 @error('renderedAmount') border-red-400 @enderror">
                        @error('renderedAmount')
                            <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                        @enderror
                    @else
                        <label class="block text-sm text-slate-600 mb-1">Reference No.:</label>
                        <input type="text" readonly value="{{ $referenceNo }}" placeholder="Enter Reference No."
                               class="w-full border rounded px-2 py-1 mb-1 text-sm bg-slate-50 @error('referenceNo') border-red-400 @enderror">
                        @error('referenceNo')
                            <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                        @enderror
                    @endif

                    <div class="grid grid-cols-3 gap-2 mb-3">
                        @foreach (['1','2','3','4','5','6','7','8','9'] as $digit)
                            <button wire:click="pressKey('{{ $digit }}')" class="bg-slate-600 hover:bg-slate-700 text-white py-2 rounded">{{ $digit }}</button>
                        @endforeach
                        <button wire:click="clearKeypad" class="bg-slate-300 hover:bg-slate-400 text-white py-2 rounded text-xs">C</button>
                        <button wire:click="pressKey('0')" class="bg-slate-600 hover:bg-slate-700 text-white py-2 rounded">0</button>
                        <button wire:click="pressKey('.')" class="bg-slate-600 hover:bg-slate-700 text-white py-2 rounded">.</button>
                    </div>

                    <p class="text-sm mb-4">Change: ${{ number_format($this->change, 2) }}</p>

                    <div class="flex gap-3">
                        <button wire:click="backToDiscount" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white py-2 rounded text-sm">Back</button>
                        <button wire:click="proceedPayment" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white py-2 rounded text-sm">Proceed</button>
                    </div>
                @else
                    <h2 class="text-2xl font-semibold text-center mb-6">Payment Successful</h2>
                    <button wire:click="finishTransaction" class="w-full bg-slate-600 hover:bg-slate-700 text-white py-2 rounded font-medium">Okay</button>
                @endif
            </div>
        </div>
    @endif
</div>