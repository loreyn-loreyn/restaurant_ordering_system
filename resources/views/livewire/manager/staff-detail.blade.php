<div class="flex flex-col h-full">

    {{-- ── Top bar ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-6 py-3 border-b bg-white shrink-0">
        <a href="{{ route('manager.staffs') }}" wire:navigate class="text-slate-400 hover:text-slate-600 text-lg">&larr;</a>
        <div>
            <span class="text-lg font-semibold text-slate-800">{{ $staff->FullName }}</span>
            <span class="text-xs text-slate-400 ml-2">{{ $staff->StaffID }}</span>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="grid grid-cols-2 gap-6">

            {{-- ── Left column: profile ────────────────────────────── --}}
            <div class="space-y-4">

                {{-- Photo + section + account status --}}
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4 flex items-center gap-4">
                    <div class="w-20 h-20 rounded bg-slate-200 flex items-center justify-center text-slate-400 text-3xl overflow-hidden shrink-0">
                        @if ($staff->PhotoUrl)
                            <img src="{{ $staff->PhotoUrl }}" alt="{{ $staff->FullName }}" class="w-full h-full object-cover">
                        @else
                            &#128100;
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $staff->Section ?? 'No section yet' }}</p>
                        @if ($staff->HasAccount && $staff->user->AccountApprovalStatus)
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Account: Approved
                            </span>
                        @else
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                Account not yet created by Admin
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Personal information --}}
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Personal Information</p>
                    <div class="grid grid-cols-2 gap-y-2.5 text-sm">
                        <span class="text-slate-400">Age</span>
                        <span class="text-slate-700">{{ $staff->Age }}</span>
                        <span class="text-slate-400">Birth date</span>
                        <span class="text-slate-700">{{ $staff->BirthDate->format('M d, Y') }}</span>
                        <span class="text-slate-400">Sex</span>
                        <span class="text-slate-700">{{ $staff->Sex === 'M' ? 'Male' : 'Female' }}</span>
                        <span class="text-slate-400">Birth place</span>
                        <span class="text-slate-700">{{ $staff->BirthPlace }}</span>
                        <span class="text-slate-400">Nationality</span>
                        <span class="text-slate-700">{{ $staff->Nationality }}</span>
                    </div>
                </div>

                {{-- Contact information --}}
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Contact Information</p>
                    <div class="grid grid-cols-2 gap-y-2.5 text-sm">
                        <span class="text-slate-400">Contact</span>
                        <span class="text-slate-700">{{ $staff->ContactNumber }}</span>
                        <span class="text-slate-400">Email</span>
                        <span class="text-slate-700 truncate">{{ $staff->Email }}</span>
                        <span class="text-slate-400">Address</span>
                        <span class="text-slate-700">{{ $staff->Address }}</span>
                    </div>
                </div>

                {{-- Employment --}}
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Employment</p>
                    <div class="grid grid-cols-2 gap-y-2.5 text-sm">
                        <span class="text-slate-400">Hired date</span>
                        <span class="text-slate-700">{{ $staff->HiredDate->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- ── Right column: attendance ────────────────────────── --}}
            <div>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-3 text-center">
                        <p class="text-xs text-slate-500">Present</p>
                        <p class="text-xl font-semibold text-slate-800">{{ $presentCount }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-3 text-center">
                        <p class="text-xs text-slate-500">Absence</p>
                        <p class="text-xl font-semibold text-slate-800">{{ $absentCount }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-3 text-center">
                        <p class="text-xs text-slate-500">Leaves</p>
                        <p class="text-xl font-semibold text-slate-800">{{ $leaveCount }}</p>
                    </div>
                </div>

                <p class="text-sm font-semibold text-slate-700 mb-2">Attendance</p>

                <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">

                    {{-- Month nav --}}
                    <div class="flex items-center justify-between mb-3">
                        <button wire:click="prevMonth" class="text-slate-400 hover:text-slate-600 px-2">&lsaquo;</button>
                        <span class="text-sm font-medium text-slate-700">{{ $monthStart->format('F Y') }}</span>
                        <button wire:click="nextMonth" class="text-slate-400 hover:text-slate-600 px-2">&rsaquo;</button>
                    </div>

                    {{-- Weekday headers --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] text-slate-400 mb-1">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)
                            <span>{{ $d }}</span>
                        @endforeach
                    </div>

                    {{-- Day grid --}}
                    <div class="grid grid-cols-7 gap-1 mb-4">
                        @php
                            $leadingBlanks = $monthStart->dayOfWeekIso - 1;
                            $daysInMonth = $monthStart->daysInMonth;
                        @endphp
                        @for ($i = 0; $i < $leadingBlanks; $i++)
                            <span></span>
                        @endfor
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = $monthStart->copy()->day($day)->toDateString();
                                $att = $monthAttendances->get($dateStr);
                                $isSelected = $dateStr === $selectedDate;
                            @endphp
                            <button wire:click="selectDate('{{ $dateStr }}')"
                                    class="w-8 h-8 rounded-full text-xs flex items-center justify-center relative transition
                                           {{ $isSelected ? 'bg-slate-700 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                                {{ $day }}
                                @if ($att && ! $isSelected)
                                    <span class="absolute bottom-0.5 w-1 h-1 rounded-full
                                        {{ $att->Status === 'P' ? 'bg-green-500' : ($att->Status === 'L' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                @endif
                            </button>
                        @endfor
                    </div>

                    {{-- Status buttons --}}
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <button wire:click="markStatus('A')"
                                class="py-1.5 rounded text-sm font-medium transition
                                       {{ $selectedAttendance?->Status === 'A' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Absent
                        </button>
                        <button wire:click="markStatus('P')"
                                class="py-1.5 rounded text-sm font-medium transition
                                       {{ $selectedAttendance?->Status === 'P' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Present
                        </button>
                        <button wire:click="markStatus('L')"
                                class="py-1.5 rounded text-sm font-medium transition
                                       {{ $selectedAttendance?->Status === 'L' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Leave
                        </button>
                    </div>

                    {{-- Note --}}
                    <label class="block text-xs text-slate-500 mb-1">Note for Selected Date</label>
                    <textarea wire:model="noteText" wire:blur="saveNote" rows="2" placeholder="e.g. Sick leave"
                              class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>