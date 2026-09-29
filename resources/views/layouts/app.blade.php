<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Sistem Kandang') }} — {{ config('app.name', 'Sistem Kandang') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Sidebar light memakai state collapse bersama; label disembunyikan
         saat collapsed (§7.11), icon tetap dengan tooltip. --}}
    <style>
        [x-cloak] { display: none !important; }

        @media (min-width: 768px) {
            .app-collapsed .sidebar-label { display: none; }
            .app-collapsed aside { width: 72px; }
            .app-collapsed .sidebar-item { justify-content: center; padding-inline: 0; }
            .app-collapsed .brand-mark { margin-inline: auto; }
        }
    </style>
</head>
<body class="min-h-screen bg-neutral-50 antialiased"
      x-data="appShell()"
      x-bind:class="sidebarCollapsed ? 'app-collapsed' : ''"
      @keydown.escape.window="sidebarOpen = false">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-primary-500 focus:px-4 focus:py-2 focus:text-sm focus:text-white">
        Lewati ke konten
    </a>

    {{-- Overlay mobile: klik menutup drawer (§7.13) --}}
    <div x-show="sidebarOpen" x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-neutral-900/40 md:hidden"
         aria-hidden="true"></div>

    @include('layouts.sidebar')

    <div class="flex min-h-screen flex-col md:pl-sidebar"
         :class="sidebarCollapsed ? 'md:pl-sidebar-collapsed' : ''">

        @include('layouts.topbar')

        <main id="main-content" class="flex-1 px-4 py-6 md:px-8">
            <div class="mx-auto w-full max-w-[1440px] space-y-5">
                @if (session('status'))
                    <x-alert variant="success">{{ session('status') }}</x-alert>
                @endif

                @if ($errors->any())
                    <x-alert variant="error" title="Periksa kembali isian berikut">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
