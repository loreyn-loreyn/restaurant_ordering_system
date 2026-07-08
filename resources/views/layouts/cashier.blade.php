<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJT Onboarding') }} - Cashier</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-white">
    <div class="min-h-screen flex bg-white">

        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        {{-- Content comes from the component's own view via <x-slot name="sidebar">,
             so wire:click handlers inside it are actually part of the Livewire
             component's root and will work. This layout only provides structure. --}}
        <aside class="w-48 bg-slate-700 text-white flex flex-col py-6 shrink-0">
            <h1 class="px-6 text-xl font-semibold mb-6">Menu</h1>

            {{ $sidebar ?? '' }}
        </aside>

        {{-- ── Main ────────────────────────────────────────────────────── --}}
        <main class="flex-1 relative overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>