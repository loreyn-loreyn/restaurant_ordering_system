<div class="min-h-screen flex flex-col bg-white">
    <div class="bg-slate-500 h-56 flex items-center justify-center overflow-hidden">
        <span class="text-white text-5xl">&#128247;</span>
    </div>

    <div class="flex-1 flex flex-col items-center justify-start pt-16 gap-6">
        <button
            wire:click="selectType('Dine-in')"
            wire:loading.attr="disabled"
            class="w-72 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-4 rounded transition"
        >
            Dine-in
        </button>

        <button
            wire:click="selectType('Take-out')"
            wire:loading.attr="disabled"
            class="w-72 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-4 rounded transition"
        >
            Take-out
        </button>
    </div>

    <div class="px-6 pb-6">
        <button wire:click="signOut" class="text-sm text-slate-400 hover:text-slate-600">
            Sign Out
        </button>
    </div>
</div>