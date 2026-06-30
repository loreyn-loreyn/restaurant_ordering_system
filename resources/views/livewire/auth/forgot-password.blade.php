<div class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-sm">
        <div class="flex items-center gap-2 mb-4 text-purple-600 font-semibold">
            <span>&#10070;</span>
            <span>Signup</span>
        </div>

        <div class="bg-slate-100 rounded-md shadow p-8">
            <h1 class="text-3xl font-semibold text-slate-700 mb-6">Forget Password</h1>

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Contact Number</label>
                    <input
                        type="text"
                        wire:model="contactNumber"
                        class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                        placeholder="Input text"
                    >
                    @error('contactNumber')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">New Password</label>
                    <input
                        type="password"
                        wire:model="password"
                        class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">Confirm Password</label>
                    <input
                        type="password"
                        wire:model="passwordConfirmation"
                        class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                    >
                    @error('passwordConfirmation')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-slate-600 hover:bg-slate-700 text-white font-medium py-2 rounded transition"
                    wire:loading.attr="disabled"
                >
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
