<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJT Onboarding') }} - Manager</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-slate-100">
    <div class="h-screen flex bg-slate-100 overflow-hidden">

        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        <aside class="w-48 bg-slate-700 text-white flex flex-col py-6 shrink-0">
            <h1 class="px-6 text-xl font-semibold mb-6">Manager</h1>

            <a href="{{ route('manager.sales') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('manager.sales') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                Sales
            </a>
            <a href="{{ route('manager.dishes') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('manager.dishes') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                Dishes
            </a>
            <a href="{{ route('manager.staffs') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('manager.staffs') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                Staffs
            </a>

            <div class="mt-auto px-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 text-sm text-slate-300 hover:text-white py-2 transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main ────────────────────────────────────────────────────── --}}
        <main class="flex-1 flex flex-col overflow-hidden">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>