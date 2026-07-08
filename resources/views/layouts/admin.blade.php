<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJT Onboarding') }} - Admin</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-slate-100">
    <div class="h-screen flex bg-slate-100 overflow-hidden">

        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        <aside class="w-52 bg-slate-700 text-white flex flex-col py-6 shrink-0">
            <h1 class="px-6 text-xl font-semibold mb-6">Admin</h1>

            <a href="{{ route('admin.dashboard') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('admin.dashboard') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7l9-4 9 4M4 10v9a1 1 0 001 1h4v-6h6v6h4a1 1 0 001-1v-9" />
                </svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('admin.users') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Users
            </a>

            <div class="mt-auto px-6 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 text-sm text-slate-300 hover:text-white py-2 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main ────────────────────────────────────────────────────── --}}
        <main class="flex-1 flex flex-col overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>