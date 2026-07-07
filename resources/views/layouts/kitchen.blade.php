<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJT Onboarding') }} - Kitchen</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-slate-100">
    <div class="h-screen flex bg-slate-100 overflow-hidden">

        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        <aside class="w-48 bg-slate-700 text-white flex flex-col py-6 shrink-0">
            <h1 class="px-6 text-xl font-semibold mb-6">Kitchen</h1>

            <a href="{{ route('kitchen.orders') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('kitchen.orders') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                Orders
            </a>
            <a href="{{ route('kitchen.availability') }}" wire:navigate
               class="flex items-center gap-3 px-6 py-3 text-sm transition
                      {{ request()->routeIs('kitchen.availability') ? 'bg-slate-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                Availability
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
        <main class="flex-1 flex flex-col overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
