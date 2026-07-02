<div class="min-h-screen flex bg-slate-100">

    {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
    <aside class="w-48 bg-slate-700 text-white flex flex-col py-6 shrink-0">
        <h1 class="px-6 text-xl font-semibold mb-6">Kitchen</h1>

        <a href="{{ route('kitchen.orders') }}" wire:navigate
           class="flex items-center gap-3 px-6 py-3 text-slate-300 hover:bg-slate-600 hover:text-white text-sm transition">
            Orders
        </a>
        <a href="{{ route('kitchen.availability') }}" wire:navigate
           class="flex items-center gap-3 px-6 py-3 bg-slate-600 text-white font-medium text-sm">
            Availability
        </a>

        <div class="mt-auto px-6">
            <button wire:click="signOut"
                    class="flex items-center gap-2 text-sm text-slate-300 hover:text-white py-2 transition">
                Sign Out
            </button>
        </div>
    </aside>

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

            {{-- Live clock --}}
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

        {{-- ── Dish grid ─────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-3 gap-4">
                @foreach ($dishes as $dish)
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col transition
                                {{ ! $dish->Availability ? 'opacity-50' : '' }}">

                        {{-- Dish image placeholder --}}
                        <div class="bg-slate-200 h-36 flex items-center justify-center text-slate-400 text-4xl shrink-0">
                            &#128247;
                        </div>

                        <div class="p-3 flex flex-col gap-2">
                            <p class="font-semibold text-sm text-slate-800 text-center">{{ $dish->DishName }}</p>

                            {{-- Toggle button: appearance reflects current state --}}
                            <button
                                wire:click="toggleAvailability({{ $dish->DishID }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleAvailability({{ $dish->DishID }})"
                                class="w-full py-1.5 rounded-full text-sm font-medium transition
                                       {{ $dish->Availability
                                            ? 'bg-slate-700 hover:bg-slate-800 text-white'
                                            : 'bg-slate-200 hover:bg-slate-300 text-slate-600' }}">
                                {{ $dish->Availability ? 'Available' : 'Not Available' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>
</div>