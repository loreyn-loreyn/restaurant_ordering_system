<div class="flex flex-col h-full relative">

    {{-- ── Top bar ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-6 py-3 border-b bg-white shrink-0">
        <a href="{{ route('manager.staffs') }}" wire:navigate class="text-slate-400 hover:text-slate-600 text-lg">&larr;</a>
        <span class="text-lg font-semibold text-slate-800">
            {{ $editingStaffId ? 'Edit Staff Info' : 'New Staff Info' }}
        </span>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto space-y-6">

            {{-- Photo --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4 flex items-center gap-4">
                <div class="w-20 h-20 rounded bg-slate-200 flex items-center justify-center overflow-hidden shrink-0 text-3xl text-slate-400">
                    @if ($Photo)
                        <img src="{{ $Photo->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($existingPhoto)
                        <img src="{{ asset('storage/' . $existingPhoto) }}" class="w-full h-full object-cover">
                    @else
                        &#128100;
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Photo</p>
                    <input type="file" wire:model="Photo" accept="image/png, image/jpeg, image/jpg"
                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:bg-slate-100 file:text-slate-600 file:text-xs">
                    <div wire:loading wire:target="Photo" class="text-xs text-slate-400 mt-1">Uploading...</div>
                    @error('Photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Personal Information</p>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Last Name</label>
                        <input type="text" wire:model="LastName" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('LastName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">First Name</label>
                        <input type="text" wire:model="FirstName" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('FirstName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Middle Name</label>
                        <input type="text" wire:model="MiddleName" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Age</label>
                        <input type="number" wire:model="Age" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('Age') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-slate-500 mb-1">Birth Date</label>
                        <input type="date" wire:model.live="BirthDate" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('BirthDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Sex</label>
                        <select wire:model="Sex" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                            <option value="">-</option>
                            <option value="M">M</option>
                            <option value="F">F</option>
                        </select>
                        @error('Sex') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Birth Place</label>
                        <input type="text" wire:model="BirthPlace" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('BirthPlace') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nationality</label>
                        <input type="text" wire:model="Nationality" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('Nationality') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Contact Information</p>

                <div class="mb-4">
                    <label class="block text-xs text-slate-500 mb-1">Address</label>
                    <input type="text" wire:model="Address" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    @error('Address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Contact Number</label>
                        <input type="text" wire:model="ContactNumber" placeholder="09171234567"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        <p class="text-[11px] text-slate-400 mt-1">Format: 09171234567 or +639171234567</p>
                        @error('ContactNumber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Email</label>
                        <input type="email" wire:model="Email" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('Email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Employment --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Employment</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Assigned Role</label>
                        <select wire:model="AssignedRoleID" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->RoleID }}">{{ $role->RoleName }}</option>
                            @endforeach
                        </select>
                        @error('AssignedRoleID') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Hired Date</label>
                        <input type="date" wire:model="HiredDate" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @error('HiredDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            @unless ($editingStaffId)
                <p class="text-xs text-slate-400">
                    Submitting sends this staff's details to the Admin, who will review it and create their login account.
                </p>
            @endunless

            <div class="flex gap-3 max-w-md mx-auto pb-2">
                <button type="button" wire:click="discard"
                        class="flex-1 bg-slate-100 text-slate-700 text-sm font-medium py-2.5 rounded hover:bg-slate-200 transition">
                    Discard
                </button>
                <button wire:click="confirmSubmit"
                        class="flex-1 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium py-2.5 rounded transition">
                    Submit
                </button>
            </div>
        </div>
    </div>

    {{-- ── Confirm overlay ────────────────────────────────────────── --}}
    @if ($confirming)
        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-80 p-5 text-center">
                <p class="text-sm text-slate-700 mb-4">
                    Are you sure that all of the information you entered is correct?
                </p>
                <div class="flex gap-3">
                    <button wire:click="cancelConfirm"
                            class="flex-1 bg-slate-100 text-slate-700 text-sm font-medium py-2 rounded hover:bg-slate-200 transition">
                        No
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium py-2 rounded transition">
                        <span wire:loading.remove wire:target="save">Yes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>