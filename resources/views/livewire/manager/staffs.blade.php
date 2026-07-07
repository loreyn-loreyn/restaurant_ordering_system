<div class="flex flex-col h-full">

    {{-- ── Top bar: section pills ─────────────────────────────────── --}}
    <div class="flex items-center px-6 py-3 border-b bg-white shrink-0">
        <div class="flex gap-2 flex-wrap">
            <button wire:click="filterByRole(null)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                           {{ is_null($roleFilter) ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All
            </button>
            <button wire:click="filterByRole('unassigned')"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                           {{ $roleFilter === 'unassigned' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Pending Account
            </button>
            @foreach ($roles as $role)
                <button wire:click="filterByRole({{ $role->RoleID }})"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition
                               {{ $roleFilter === $role->RoleID ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $role->RoleName }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Staff grid ─────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto p-6 relative">
        <div class="grid grid-cols-3 gap-4">
            @forelse ($staff as $s)
                @php $todayAttendance = $s->attendances->first(); @endphp
                <a href="{{ route('manager.staff.detail', $s->StaffID) }}" wire:navigate
                   x-data="{ pressTimer: null, longPressed: false }"
                   @mousedown="longPressed = false; pressTimer = setTimeout(() => { longPressed = true; $wire.openActionMenu('{{ $s->StaffID }}') }, 600)"
                   @mouseup="clearTimeout(pressTimer)"
                   @mouseleave="clearTimeout(pressTimer)"
                   @touchstart.passive="longPressed = false; pressTimer = setTimeout(() => { longPressed = true; $wire.openActionMenu('{{ $s->StaffID }}') }, 600)"
                   @touchend="clearTimeout(pressTimer)"
                   @contextmenu.prevent
                   @click="if (longPressed) { $event.preventDefault() }"
                   class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col hover:shadow-md transition select-none">
                    <div class="bg-slate-200 h-28 flex items-center justify-center text-slate-400 text-3xl shrink-0 overflow-hidden">
                        @if ($s->PhotoUrl)
                            <img src="{{ $s->PhotoUrl }}" alt="{{ $s->FullName }}" class="w-full h-full object-cover">
                        @else
                            &#128100;
                        @endif
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-sm text-slate-800">Name: {{ $s->FullName }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            Section: {{ $s->Section ?? 'Pending account' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Attendance Status:
                            @if (! $todayAttendance)
                                <span class="text-slate-400">Not marked</span>
                            @elseif ($todayAttendance->Status === 'P')
                                <span class="text-green-600 font-medium">Present</span>
                            @elseif ($todayAttendance->Status === 'L')
                                <span class="text-amber-600 font-medium">Leave</span>
                            @else
                                <span class="text-red-500 font-medium">Absent</span>
                            @endif
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center text-slate-400 py-16 text-sm">
                    No staff in this section yet.
                </div>
            @endforelse
        </div>

        {{-- Floating add button — goes to the full New Staff Info page --}}
        <a href="{{ route('manager.staff.create') }}" wire:navigate
           class="fixed bottom-8 right-8 bg-slate-700 hover:bg-slate-800 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg text-2xl">
            +
        </a>
    </div>

    {{-- ── Long-press action menu (Edit / Delete) ─────────────────── --}}
    @if ($actionMenuStaff)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="closeActionMenu">
            <div class="bg-white rounded-lg shadow-xl w-72 p-4">
                <p class="text-sm font-semibold text-slate-800 mb-4 text-center">{{ $actionMenuStaff->FullName }}</p>
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

    {{-- ── Long-press delete confirmation ─────────────────────────── --}}
    @if ($pendingDeleteStaff)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="cancelDelete">
            <div class="bg-white rounded-lg shadow-xl w-80 p-5 text-center">
                <p class="text-sm text-slate-700 mb-1">Delete this staff record?</p>
                <p class="text-sm font-semibold text-slate-800 mb-4">{{ $pendingDeleteStaff->FullName }}</p>
                <p class="text-xs text-slate-400 mb-4">This cannot be undone.</p>
                <div class="flex gap-3">
                    <button wire:click="cancelDelete"
                            class="flex-1 bg-slate-100 text-slate-700 text-sm font-medium py-2 rounded hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button wire:click="deleteStaff" wire:loading.attr="disabled" wire:target="deleteStaff"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 rounded transition">
                        <span wire:loading.remove wire:target="deleteStaff">Delete</span>
                        <span wire:loading wire:target="deleteStaff">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>