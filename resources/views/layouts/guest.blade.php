<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Kandang') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 font-sans text-neutral-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full sm:max-w-md">
            <div class="mb-6 flex flex-col items-center gap-2.5 text-center">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-500 text-white" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75c-2.2 0-4 1.5-4 3.4 0 .6.2 1.2.5 1.7-.9.4-1.5 1.2-1.5 2.2 0 .9.5 1.7 1.3 2.1-.2.4-.3.8-.3 1.2 0 1.7 1.8 3.1 4 3.1s4-1.4 4-3.1c0-.4-.1-.8-.3-1.2.8-.4 1.3-1.2 1.3-2.1 0-1-.6-1.8-1.5-2.2.3-.5.5-1.1.5-1.7 0-1.9-1.8-3.4-4-3.4Z" />
                    </svg>
                </span>
                <span class="text-[15px] font-bold tracking-tight text-neutral-900">Sistem Kandang</span>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
