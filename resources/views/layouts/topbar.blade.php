@php $unread = \App\Models\Notification::whereNull('read_at')->count(); @endphp

{{-- §8 — tinggi 64px desktop / 56px mobile, sticky, putih dengan border bawah. --}}
<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-neutral-200 bg-white px-4 md:px-6 lg:px-8">
    {{-- Toggle sidebar: drawer di mobile, collapse/expand di desktop (§7.10). --}}
    <button
        type="button"
        class="-ml-1 flex h-10 w-10 items-center justify-center rounded-lg text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-neutral-900"
        @click="window.innerWidth < 768 ? (sidebarOpen = !sidebarOpen) : toggleCollapsed()"
        :aria-expanded="(sidebarOpen || sidebarCollapsed) ? 'true' : 'false'"
        aria-controls="app-sidebar"
        aria-label="Buka menu navigasi">
        <x-heroicon-o-bars-3 class="h-5 w-5" />
    </button>

    <h1 class="min-w-0 flex-1 truncate text-lg font-semibold text-neutral-900">
        {{ $title ?? 'Dashboard' }}
    </h1>

    {{-- Notifikasi dengan badge jumlah belum dibaca (§7.8). --}}
    <a href="{{ route('notifications.index') }}"
       class="relative flex h-10 w-10 items-center justify-center rounded-lg text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-neutral-900"
       aria-label="Notifikasi{{ $unread ? ', '.$unread.' belum dibaca' : '' }}">
        <x-heroicon-o-bell class="h-5 w-5" />
        @if ($unread > 0)
            <span class="absolute right-1 top-1 flex h-4 min-w-[16px] items-center justify-center rounded-pill bg-primary-500 px-1 text-[10px] font-semibold leading-none text-white">
                {{ $unread > 99 ? '99+' : $unread }}
            </span>
        @endif
    </a>
</header>
