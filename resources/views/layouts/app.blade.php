<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJT Onboarding') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-white">
    {{ $slot }}
    @livewireScripts
</body>
</html>
