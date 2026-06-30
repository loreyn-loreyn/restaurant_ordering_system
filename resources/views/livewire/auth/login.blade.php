<div class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-sm">

        <div class="bg-slate-100 rounded-md shadow p-8">
            <h1 class="text-3xl font-semibold text-slate-700 text-center mb-6">Login</h1>

            <form wire:submit="login" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Username</label>
                    <input
                        type="text"
                        wire:model="username"
                        class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                        placeholder=""
                    >
                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">Password</label>
                    <div class="relative">
                        <input
                            type="{{ $showPassword ? 'text' : 'password' }}"
                            wire:model="password"
                            class="w-full rounded border border-slate-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-slate-400"
                            placeholder="Input text"
                        >
                        <button
                            type="button"
                            wire:click="togglePassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                        >
                            &#128065;
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-slate-600 hover:bg-slate-700 text-white font-medium py-2 rounded transition"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="login">Login</span>
                    <span wire:loading wire:target="login">Logging in...</span>
                </button>

                <div class="text-center">
                    <a href="{{ route('password.forgot') }}" wire:navigate class="text-sm text-slate-500 hover:underline">
                        Forgot password
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
