@php
    /**
     * groups: ['label' => ..., 'items' => ['route' => ['label','icon','pattern']]]
     * pattern dipakai request()->routeIs() untuk menandai active.
     */
    $groups = [
        [
            'label' => 'Utama',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'o-squares-2x2', 'pattern' => 'dashboard'],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['route' => 'pigs.index', 'label' => 'Data Babi', 'icon' => 'o-cube', 'pattern' => 'pigs.*'],
                ['route' => 'population.index', 'label' => 'Populasi', 'icon' => 'o-chart-bar', 'pattern' => 'population.*'],
                ['route' => 'weighings.index', 'label' => 'Penimbangan', 'icon' => 'o-scale', 'pattern' => 'weighings.*'],
                ['route' => 'movements.index', 'label' => 'Perpindahan', 'icon' => 'o-arrows-right-left', 'pattern' => 'movements.*'],
                ['route' => 'health.index', 'label' => 'Kesehatan', 'icon' => 'o-beaker', 'pattern' => 'health.*'],
                ['route' => 'reproduction.index', 'label' => 'Reproduksi', 'icon' => 'o-heart', 'pattern' => 'reproduction.*'],
                ['route' => 'births.index', 'label' => 'Kelahiran', 'icon' => 'o-sparkles', 'pattern' => 'births.*'],
                ['route' => 'deaths.index', 'label' => 'Kematian', 'icon' => 'o-exclamation-triangle', 'pattern' => 'deaths.*'],
                ['route' => 'feeds.index', 'label' => 'Pakan', 'icon' => 'o-inbox-stack', 'pattern' => 'feeds.*'],
                ['route' => 'sales.index', 'label' => 'Penjualan', 'icon' => 'o-banknotes', 'pattern' => 'sales.*'],
                ['route' => 'purchase.index', 'label' => 'Pembelian', 'icon' => 'o-shopping-cart', 'pattern' => 'purchase.*'],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['route' => 'reports.index', 'label' => 'Laporan', 'icon' => 'o-document-text', 'pattern' => 'reports.*'],
                ['route' => 'finance.index', 'label' => 'Keuangan', 'icon' => 'o-calculator', 'pattern' => 'finance.*'],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['route' => 'branches.index', 'label' => 'Cabang', 'icon' => 'o-building-office', 'pattern' => 'branches.*'],
                ['route' => 'areas.index', 'label' => 'Area', 'icon' => 'o-map', 'pattern' => 'areas.*'],
                ['route' => 'pens.index', 'label' => 'Kandang', 'icon' => 'o-home-modern', 'pattern' => 'pens.*'],
                ['route' => 'breeds.index', 'label' => 'Jenis Babi', 'icon' => 'o-tag', 'pattern' => 'breeds.*'],
                ['route' => 'phases.index', 'label' => 'Fase Babi', 'icon' => 'o-chart-pie', 'pattern' => 'phases.*'],
                ['route' => 'units.index', 'label' => 'Satuan', 'icon' => 'o-adjustments-horizontal', 'pattern' => 'units.*'],
                ['route' => 'feed-types.index', 'label' => 'Jenis Pakan', 'icon' => 'o-cake', 'pattern' => 'feed-types.*'],
                ['route' => 'medicines.index', 'label' => 'Obat & Vaksin', 'icon' => 'o-beaker', 'pattern' => 'medicines.*'],
                ['route' => 'diseases.index', 'label' => 'Penyakit', 'icon' => 'o-bug-ant', 'pattern' => 'diseases.*'],
                ['route' => 'suppliers.index', 'label' => 'Supplier', 'icon' => 'o-truck', 'pattern' => 'suppliers.*'],
                ['route' => 'customers.index', 'label' => 'Pelanggan', 'icon' => 'o-user-group', 'pattern' => 'customers.*'],
            ],
        ],
    ];
@endphp

{{-- Sidebar light (Design.md §7). 248px expanded / 72px collapsed, fixed, full height. --}}
<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-sidebar flex-col border-r border-neutral-200 bg-white
           transition-[width,transform] duration-200 ease-out
           -translate-x-full md:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : ''"
    aria-label="Navigasi utama">

    {{-- Brand (§7.2) --}}
    <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-neutral-200 px-4">
        <span class="brand-mark flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-500 text-white" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75c-2.2 0-4 1.5-4 3.4 0 .6.2 1.2.5 1.7-.9.4-1.5 1.2-1.5 2.2 0 .9.5 1.7 1.3 2.1-.2.4-.3.8-.3 1.2 0 1.7 1.8 3.1 4 3.1s4-1.4 4-3.1c0-.4-.1-.8-.3-1.2.8-.4 1.3-1.2 1.3-2.1 0-1-.6-1.8-1.5-2.2.3-.5.5-1.1.5-1.7 0-1.9-1.8-3.4-4-3.4Z" />
            </svg>
        </span>
        <span class="truncate text-[15px] font-bold tracking-tight text-neutral-900 sidebar-label">Sistem Kandang</span>
    </div>

    {{-- Menu (§7.3) --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4">
        @foreach ($groups as $group)
            <div class="mb-5 last:mb-0">
                <p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 sidebar-label">
                    {{ $group['label'] }}
                </p>

                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php $isActive = request()->routeIs($item['pattern']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if ($isActive) aria-current="page" @endif
                               title="{{ $item['label'] }}"
                               class="group flex h-10 items-center gap-3 rounded-lg px-3 text-sm transition-colors sidebar-item
                                      {{ $isActive
                                          ? 'bg-primary-50 font-medium text-primary-700'
                                          : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900' }}">

                                <x-dynamic-component
                                    :component="'heroicon-'.$item['icon']"
                                    class="h-5 w-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-neutral-500 group-hover:text-neutral-700' }}" />

                                <span class="sidebar-label truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- User account (§7.14) --}}
    <div class="shrink-0 border-t border-neutral-200 p-3">
        <x-dropdown align="left" width="60">
            <x-slot name="trigger">
                <button type="button" class="flex w-full items-center gap-3 rounded-lg p-2 text-left transition-colors hover:bg-neutral-100">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-pill bg-primary-100 text-[13px] font-semibold text-primary-700" aria-hidden="true">
                        {{ Str::of(Auth::user()->name ?? '?')->substr(0, 2)->upper() }}
                    </span>
                    <span class="sidebar-label min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-neutral-800">{{ Auth::user()->name }}</span>
                        <span class="block truncate text-xs text-neutral-500">{{ Auth::user()->roleName }}</span>
                    </span>
                    <svg class="sidebar-label h-4 w-4 shrink-0 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="py-1">
                    <x-dropdown-link :href="route('profile.edit')" class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-100">
                        <x-heroicon-o-user class="h-4 w-4 text-neutral-500" /> Profil
                    </x-dropdown-link>

                    <x-dropdown-link :href="route('notifications.index')" class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-100">
                        <x-heroicon-o-bell class="h-4 w-4 text-neutral-500" /> Notifikasi
                    </x-dropdown-link>

                    <div class="my-1 border-t border-neutral-100"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link
                            :href="route('logout')"
                            class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-error-600 hover:bg-error-50"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" /> Keluar
                        </x-dropdown-link>
                    </form>
                </div>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
