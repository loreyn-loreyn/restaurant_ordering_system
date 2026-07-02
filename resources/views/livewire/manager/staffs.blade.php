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
                   class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col hover:shadow-md transition">
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
</div>