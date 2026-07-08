<div class="flex flex-col h-full">

    {{-- ── Top bar: category pills ────────────────────────────────── --}}
    <div class="flex items-center px-6 py-3 border-b bg-white shrink-0">
        <div class="flex gap-2 flex-wrap">
            <button wire:click="filterByCategory(null)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                           {{ is_null($categoryFilter) ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All
            </button>
            @foreach ($categories as $category)
                <button wire:click="filterByCategory({{ $category->CategoryID }})"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition
                               {{ $categoryFilter === $category->CategoryID ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $category->CategoryName }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Dish grid ──────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto p-6 relative">
        <div class="grid grid-cols-3 gap-4">
            @forelse ($dishes as $dish)
                <div
                    x-data="{ pressTimer: null, longPressed: false }"
                    @mousedown="longPressed = false; pressTimer = setTimeout(() => { longPressed = true; $wire.openActionMenu({{ $dish->DishID }}) }, 600)"
                    @mouseup="clearTimeout(pressTimer)"
                    @mouseleave="clearTimeout(pressTimer)"
                    @touchstart.passive="longPressed = false; pressTimer = setTimeout(() => { longPressed = true; $wire.openActionMenu({{ $dish->DishID }}) }, 600)"
                    @touchend="clearTimeout(pressTimer)"
                    @contextmenu.prevent
                    @click="if (! longPressed) { $wire.openEdit({{ $dish->DishID }}) }"
                    class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col transition select-none
                           {{ ! $dish->Availability ? 'opacity-50 pointer-events-none' : 'cursor-pointer hover:shadow-md' }}"
                >
                    <div class="bg-slate-200 h-28 flex items-center justify-center text-slate-400 text-3xl shrink-0 overflow-hidden">
                        @if ($dish->PhotoUrl)
                            <img src="{{ $dish->PhotoUrl }}" alt="{{ $dish->DishName }}" class="w-full h-full object-cover">
                        @else
                            &#128247;
                        @endif
                    </div>
                    <div class="p-3">
                        <div class="flex justify-between items-start">
                            <p class="font-semibold text-sm text-slate-800">{{ $dish->DishName }}</p>
                            <span class="text-xs text-slate-400">{{ $dish->DishCode }}</span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $dish->Description }}</p>
                        <p class="font-semibold text-sm mt-2">₱{{ number_format($dish->Price, 2) }}</p>
                        @unless ($dish->Availability)
                            <p class="text-xs text-red-500 mt-1">Currently unavailable</p>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-slate-400 py-16 text-sm">
                    No dishes in this category yet.
                </div>
            @endforelse
        </div>

        {{-- Floating add button --}}
        <button
            wire:click="openCreate"
            class="fixed bottom-8 right-8 bg-slate-700 hover:bg-slate-800 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg text-2xl"
        >
            +
        </button>
    </div>

    {{-- ── Long-press action menu (Edit / Delete) ─────────────────── --}}
    @if ($actionMenuDish)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="closeActionMenu">
            <div class="bg-white rounded-lg shadow-xl w-72 p-4">
                <p class="text-sm font-semibold text-slate-800 mb-4 text-center">{{ $actionMenuDish->DishName }}</p>
                <div class="space-y-2">
                    <button wire:click="chooseEditFromMenu"
                            class="w-full bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium py-2 rounded transition">
                        Edit
                    </button>
                    <button wire:click="chooseDeleteFromMenu"
                            class="w-full bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 rounded transition">
                        Delete
                    </button>
                    <button wire:click="closeActionMenu"
                            class="w-full bg-slate-100 text-slate-700 text-sm font-medium py-2 rounded hover:bg-slate-200 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Create / Edit modal ────────────────────────────────────── --}}
    @if ($showModal)
        {{-- Note: no wire:click.self="discard" here on purpose — the modal must
             only close via the explicit Discard or Submit buttons below. --}}
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col max-h-[85vh]">

                <div class="flex items-center justify-between px-5 py-3 border-b shrink-0">
                    <span class="font-semibold text-sm text-slate-800">
                        {{ $editingDishId ? 'Edit Dish' : 'New Dish' }}
                    </span>
                    <button wire:click="discard" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                </div>

                <form wire:submit="save" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

                    {{-- Photo --}}
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded bg-slate-200 flex items-center justify-center overflow-hidden shrink-0 text-2xl text-slate-400">
                            @if ($Photo)
                                <img src="{{ $Photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($existingPhoto)
                                <img src="{{ asset('storage/' . $existingPhoto) }}" class="w-full h-full object-cover">
                            @else
                                &#128247;
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="Photo" accept="image/png, image/jpeg, image/jpg"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:bg-slate-100 file:text-slate-600 file:text-xs">
                            <div wire:loading wire:target="Photo" class="text-xs text-slate-400 mt-1">Uploading...</div>
                            @error('Photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Dish Name</label>
                        <input type="text" wire:model="DishName"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('DishName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Dish Description</label>
                        <textarea wire:model="Description" rows="3"
                                  class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"></textarea>
                        @error('Description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Category</label>
                        <select wire:model="CategoryID"
                                class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->CategoryID }}">{{ $category->CategoryName }}</option>
                            @endforeach
                        </select>
                        @error('CategoryID') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Price</label>
                            <input type="number" step="0.01" min="0" wire:model="Price"
                                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                            @error('Price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Code</label>
                            <input type="text" wire:model="DishCode"
                                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                            @error('DishCode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Choices (optional, up to 4)</label>
                        <div class="space-y-2">
                            @foreach ($choices as $index => $choice)
                                <div>
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="choices.{{ $index }}" maxlength="100"
                                               placeholder="e.g. Spicy"
                                               class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                                        <button type="button" wire:click="removeChoice({{ $index }})"
                                                class="text-slate-400 hover:text-red-500 text-xl leading-none px-1">&times;</button>
                                    </div>
                                    @error('choices.' . $index) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                        @if (count($choices) < 4)
                            <button type="button" wire:click="addChoice"
                                    class="mt-2 text-xs text-slate-600 hover:text-slate-800 font-medium">
                                + Add choice
                            </button>
                        @endif
                        @error('choices') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @if (empty($choices))
                            <p class="text-xs text-slate-400 mt-1">No choices added — the Choice section will be hidden for this dish.</p>
                        @endif
                    </div>
                </form>

                <div class="px-5 py-4 border-t shrink-0 flex gap-3">
                    <button type="button" wire:click="discard"
                            class="flex-1 bg-slate-100 text-slate-700 text-sm font-medium py-2 rounded hover:bg-slate-200 transition">
                        Discard
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium py-2 rounded transition">
                        <span wire:loading.remove wire:target="save">Submit</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Long-press delete confirmation ─────────────────────────── --}}
    @if ($pendingDeleteDish)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="cancelDelete">
            <div class="bg-white rounded-lg shadow-xl w-80 p-5 text-center">
                <p class="text-sm text-slate-700 mb-1">Delete this dish?</p>
                <p class="text-sm font-semibold text-slate-800 mb-4">{{ $pendingDeleteDish->DishName }}</p>
                <p class="text-xs text-slate-400 mb-4">This cannot be undone.</p>
                <div class="flex gap-3">
                    <button wire:click="cancelDelete"
                            class="flex-1 bg-slate-100 text-slate-700 text-sm font-medium py-2 rounded hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button wire:click="deleteDish" wire:loading.attr="disabled" wire:target="deleteDish"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 rounded transition">
                        <span wire:loading.remove wire:target="deleteDish">Delete</span>
                        <span wire:loading wire:target="deleteDish">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>