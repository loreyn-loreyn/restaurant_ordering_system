<div class="p-8 space-y-6" x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Users</h1>
            <p class="text-sm text-slate-500 mt-1">Create logins for staff added by a Manager, and manage existing accounts.</p>
        </div>
        <div class="text-right text-sm text-slate-500">
            <div x-text="now.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></div>
            <div class="font-medium text-slate-700" x-text="now.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit' }) + ' PHT'"></div>
        </div>
    </div>

    {{-- ── Top row: Pending approvals + Create account ─────────────────── --}}
    <div class="grid gap-4 items-stretch" style="grid-template-columns: 8fr 2fr;">

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden flex flex-col h-56">
            <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between shrink-0">
                <h2 class="font-semibold text-slate-800 text-sm">Pending Account Approvals</h2>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendingStaff->count() }} waiting</span>
            </div>
            <div class="flex-1 overflow-y-auto">
                @if ($pendingStaff->isEmpty())
                    <div class="h-full flex items-start justify-center pt-6 text-xs text-slate-400">
                        No staff waiting for account creation.
                    </div>
                @else
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                            <tr>
                                <th class="px-4 py-1.5 font-medium">Staff ID</th>
                                <th class="px-4 py-1.5 font-medium">Name</th>
                                <th class="px-4 py-1.5 font-medium">Job Title</th>
                                <th class="px-4 py-1.5 font-medium">Hired Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendingStaff as $staff)
                                <tr>
                                    <td class="px-4 py-1.5 text-slate-700 font-medium">{{ $staff->StaffID }}</td>
                                    <td class="px-4 py-1.5 text-slate-700">{{ $staff->full_name }}</td>
                                    <td class="px-4 py-1.5 text-slate-500">{{ $staff->role?->RoleName ?? '—' }}</td>
                                    <td class="px-4 py-1.5 text-slate-500">{{ \Carbon\Carbon::parse($staff->HiredDate)->format('m/d/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 h-56 flex flex-col">
            <h2 class="font-semibold text-slate-800 text-sm mb-3">Create User Account</h2>
            <form wire:submit="lookupStaff" class="flex gap-3 items-center">
                <div class="shrink-0 space-y-2.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Staff ID</label>
                        <input type="text" wire:model="staffId" placeholder="e.g. C006"
                               class="w-48 shrink-0 rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                        @error('staffId') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Birthdate</label>
                        <input type="date" wire:model="birthDate"
                               class="w-48 shrink-0 rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                        @error('birthDate') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button type="submit"
                        class="w-24 h-24 flex items-center justify-center shrink-0 self-center bg-slate-700 text-white text-base font-semibold rounded-lg hover:bg-slate-600 shadow-sm transition">
                    Create
                </button>
            </form>
            <p class="text-[11px] text-slate-400 mt-2">Birthdate (MMDDYYYY) becomes the temporary password.</p>
        </div>
    </div>

    {{-- ── User Basic Information ───────────────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden flex flex-col h-72">
        <div class="px-4 py-2.5 border-b border-slate-200 shrink-0">
            <h2 class="font-semibold text-slate-800 text-sm">User Basic Information</h2>
        </div>
        <div class="flex-1 overflow-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                    <tr>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">User ID</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Last Name</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">First Name</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Age</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Birthdate</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Birthplace</th>
                        <th class="px-4 py-1.5 font-medium">Address</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Contact Num</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($basicInfo as $staff)
                        <tr wire:key="basic-{{ $staff->StaffID }}">
                            <td class="px-4 py-1.5 text-slate-700 font-medium whitespace-nowrap">{{ $staff->StaffID }}</td>
                            <td class="px-4 py-1.5 text-slate-700 whitespace-nowrap">{{ $staff->LastName }}</td>
                            <td class="px-4 py-1.5 text-slate-700 whitespace-nowrap">{{ $staff->FirstName }}</td>
                            <td class="px-4 py-1.5 text-slate-500">{{ $staff->Age }}</td>
                            <td class="px-4 py-1.5 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($staff->BirthDate)->format('Y/m/d') }}</td>
                            <td class="px-4 py-1.5 text-slate-500 whitespace-nowrap">{{ $staff->BirthPlace }}</td>
                            <td class="px-4 py-1.5 text-slate-500">{{ $staff->Address }}</td>
                            <td class="px-4 py-1.5 text-slate-500 whitespace-nowrap">{{ $staff->ContactNumber }}</td>
                            <td class="px-4 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <button wire:click="openEditBasicInfo('{{ $staff->StaffID }}')"
                                            class="text-slate-600 hover:text-slate-900 transition" title="Edit basic information">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @if (($staff->role?->RoleName ?? null) !== 'Admin')
                                        <button wire:click="askDeleteStaff('{{ $staff->StaffID }}', '{{ addslashes($staff->full_name) }}')"
                                                class="text-slate-600 hover:text-red-600 transition" title="Delete staff record">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-3 text-center text-slate-400">No staff records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── User Account ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden flex flex-col h-72">
        <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between shrink-0">
            <h2 class="font-semibold text-slate-800 text-sm">User Account</h2>
            <div class="flex gap-1 text-[11px]">
                @foreach (['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                    <button wire:click="setFilter('{{ $key }}')"
                            class="px-2.5 py-1 rounded-full transition
                                   {{ $statusFilter === $key ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
        <div class="flex-1 overflow-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                    <tr>
                        <th class="px-4 py-1.5 font-medium">#</th>
                        <th class="px-4 py-1.5 font-medium">Name</th>
                        <th class="px-4 py-1.5 font-medium">User ID</th>
                        <th class="px-4 py-1.5 font-medium">Account Creation</th>
                        <th class="px-4 py-1.5 font-medium">Job Title</th>
                        <th class="px-4 py-1.5 font-medium">Status</th>
                        <th class="px-4 py-1.5 font-medium whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($accounts as $i => $staff)
                        <tr wire:key="account-{{ $staff->StaffID }}">
                            <td class="px-4 py-1.5 text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-1.5 text-slate-700">{{ $staff->full_name }}</td>
                            <td class="px-4 py-1.5 text-slate-700 font-medium">{{ $staff->StaffID }}</td>
                            <td class="px-4 py-1.5 text-slate-500">{{ \Carbon\Carbon::parse($staff->user->DateIssued)->format('d/m/Y') }}</td>
                            <td class="px-4 py-1.5 text-slate-500">{{ $staff->section ?? '—' }}</td>
                            <td class="px-4 py-1.5">
                                @if ($staff->user->AccountStatus)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] bg-slate-700 text-white">Active</span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] bg-slate-200 text-slate-600">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-1.5 whitespace-nowrap">
                                <button wire:click="askToggleStatus({{ $staff->user->UserID }}, '{{ addslashes($staff->full_name) }}', {{ $staff->user->AccountStatus ? 'true' : 'false' }})"
                                        class="text-[11px] px-2.5 py-1 rounded-full border border-slate-300 text-slate-600 hover:bg-slate-50 transition align-middle">
                                    {{ $staff->user->AccountStatus ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3 text-center text-slate-400">No accounts match this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Delete staff confirmation modal ───────────────────────────────── --}}
    @if ($confirmingDeleteStaffId)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 text-center">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Delete this staff record?</h3>
                <p class="text-sm text-slate-500 mb-5">
                    This will permanently remove <span class="font-medium text-slate-700">{{ $confirmingDeleteName }}</span> ({{ $confirmingDeleteStaffId }}) and, if they have a login, their user account as well. This can't be undone.
                </p>
                <div class="flex gap-3">
                    <button wire:click="cancelDeleteStaff"
                            class="flex-1 text-sm rounded-lg border border-slate-300 text-slate-600 py-2 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button wire:click="confirmDeleteStaff"
                            class="flex-1 text-sm rounded-lg bg-red-600 text-white py-2 hover:bg-red-500 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Activate/Deactivate confirmation modal ───────────────────────── --}}
    @if ($confirmingToggleUserId)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 text-center">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                    {{ $confirmingToggleCurrentStatus ? 'Deactivate this account?' : 'Reactivate this account?' }}
                </h3>
                <p class="text-sm text-slate-500 mb-5">
                    @if ($confirmingToggleCurrentStatus)
                        <span class="font-medium text-slate-700">{{ $confirmingToggleName }}</span> will be logged out immediately and unable to log back in until reactivated.
                    @else
                        <span class="font-medium text-slate-700">{{ $confirmingToggleName }}</span> will be able to log in again.
                    @endif
                </p>
                <div class="flex gap-3">
                    <button wire:click="cancelToggleStatus"
                            class="flex-1 text-sm rounded-lg border border-slate-300 text-slate-600 py-2 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button wire:click="confirmToggleStatus"
                            class="flex-1 text-sm rounded-lg text-white py-2 transition {{ $confirmingToggleCurrentStatus ? 'bg-red-600 hover:bg-red-500' : 'bg-slate-700 hover:bg-slate-600' }}">
                        {{ $confirmingToggleCurrentStatus ? 'Deactivate' : 'Reactivate' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Confirmation modal (create account) ─────────────────────────── --}}
    @if ($confirming && $pendingStaffPreview)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 text-center">Account Details</h3>
                <dl class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between"><dt class="text-slate-500">Staff ID</dt><dd class="text-slate-800 font-medium">{{ $pendingStaffPreview['StaffID'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Full Name</dt><dd class="text-slate-800 font-medium">{{ $pendingStaffPreview['FullName'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Position</dt><dd class="text-slate-800 font-medium">{{ $pendingStaffPreview['Position'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Birthdate</dt><dd class="text-slate-800 font-medium">{{ $pendingStaffPreview['BirthDate'] }}</dd></div>
                </dl>

                <label class="flex items-start gap-2 text-sm text-slate-600 mb-4">
                    <input type="checkbox" wire:model="confirmedCorrect" class="mt-0.5 rounded border-slate-300">
                    Is all of the provided information above correct?
                </label>
                @error('confirmedCorrect') <p class="text-xs text-red-600 mb-3">{{ $message }}</p> @enderror

                <div class="flex gap-3">
                    <button wire:click="cancelConfirm"
                            class="flex-1 text-sm rounded-lg border border-slate-300 text-slate-600 py-2 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button wire:click="createAccount"
                            class="flex-1 text-sm rounded-lg bg-slate-700 text-white py-2 hover:bg-slate-600 transition">
                        Finish
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Success modal (create account) ───────────────────────────────── --}}
    @if ($showSuccess)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 text-center">
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Account Created!</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Username: <span class="font-medium text-slate-700">{{ $createdUsername }}</span><br>
                    Temporary password: <span class="font-medium text-slate-700">{{ $createdPassword }}</span>
                </p>
                <button wire:click="closeSuccess"
                        class="w-full bg-slate-700 text-white text-sm font-medium rounded-lg py-2 hover:bg-slate-600 transition">
                    Okay
                </button>
            </div>
        </div>
    @endif

    {{-- ── Edit Basic Information modal ─────────────────────────────────── --}}
    @if ($showEditBasicInfo)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Edit Basic Information</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Staff ID: <span class="font-medium text-slate-500">{{ $editStaffId }}</span></p>
                    </div>
                    <button type="button" wire:click="closeEditBasicInfo" class="text-slate-400 hover:text-slate-600 transition rounded-lg p-1 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveBasicInfo" class="flex flex-col flex-1 min-h-0">
                    <div class="px-6 py-5 space-y-6 overflow-y-auto">

                        {{-- Personal Details --}}
                        <div>
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Personal Details</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Last Name</label>
                                    <input type="text" wire:model="editLastName" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editLastName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">First Name</label>
                                    <input type="text" wire:model="editFirstName" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editFirstName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Middle Name</label>
                                    <input type="text" wire:model="editMiddleName" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editMiddleName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sex</label>
                                    <select wire:model="editSex" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                        <option value="">Select</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                    @error('editSex') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Birthdate</label>
                                    <input type="date" wire:model.live="editBirthDate" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editBirthDate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Age</label>
                                    <input type="text" wire:model="editAge" readonly class="w-full rounded-lg border-2 border-slate-200 text-sm py-2 px-3 bg-slate-50 text-slate-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Birthplace</label>
                                    <input type="text" wire:model="editBirthPlace" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editBirthPlace') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nationality</label>
                                    <input type="text" wire:model="editNationality" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editNationality') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Contact Details --}}
                        <div class="pt-5 border-t border-slate-100">
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Contact Details</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
                                    <input type="text" wire:model="editAddress" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editAddress') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Number</label>
                                    <input type="text" wire:model="editContactNumber" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editContactNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                                    <input type="email" wire:model="editEmail" class="w-full rounded-lg border-2 border-slate-300 text-sm py-2 px-3 bg-white shadow-sm focus:border-slate-600 focus:ring-2 focus:ring-slate-200">
                                    @error('editEmail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex gap-3 px-6 py-4 border-t border-slate-200 shrink-0">
                        <button type="button" wire:click="closeEditBasicInfo"
                                class="flex-1 text-sm rounded-lg border border-slate-300 text-slate-600 py-2 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 text-sm rounded-lg bg-slate-700 text-white py-2 hover:bg-slate-600 transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


</div>