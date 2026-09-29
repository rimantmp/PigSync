<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-5">
        {{-- §10.1 — header dengan judul, deskripsi, dan aksi utama. --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-neutral-900">Ringkasan Peternakan</h2>
                <p class="mt-0.5 text-sm text-neutral-500">Kondisi farm hari ini.</p>
            </div>

            <x-primary-button href="{{ route('pigs.create') }}">
                <x-heroicon-o-plus class="h-4 w-4" />
                Registrasi Ternak
            </x-primary-button>
        </div>

        {{-- §10.2 — maksimal 2–4 card utama. --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Populasi', 'value' => $totalPopulasi, 'unit' => 'ekor hidup', 'tone' => 'text-neutral-900', 'icon' => 'o-cube'],
                ['label' => 'Sakit / Karantina', 'value' => $sakit, 'unit' => 'ekor', 'tone' => 'text-warning-600', 'icon' => 'o-exclamation-triangle'],
                ['label' => 'Kematian', 'value' => $mati, 'unit' => 'ekor bulan ini', 'tone' => 'text-error-600', 'icon' => 'o-heart'],
                ['label' => 'Kelahiran', 'value' => $lahir, 'unit' => 'piglet hidup', 'tone' => 'text-success-600', 'icon' => 'o-sparkles'],
            ] as $card)
                <div class="card-padded">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-neutral-500">{{ $card['label'] }}</p>
                        <x-dynamic-component
                            :component="'heroicon-'.$card['icon']"
                            class="h-5 w-5 shrink-0 text-neutral-300" />
                    </div>
                    <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight {{ $card['tone'] }}">
                        {{ number_format($card['value']) }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-400">{{ $card['unit'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- §10.6 — quick action maksimal 3–4, sering dipakai. --}}
            <div class="card">
                <div class="border-b border-neutral-100 px-5 py-3.5">
                    <h3 class="text-sm font-semibold text-neutral-800">Aksi Cepat</h3>
                </div>

                <div class="grid grid-cols-1 gap-px bg-neutral-100 sm:grid-cols-2">
                    @foreach ([
                        ['route' => 'pigs.create', 'label' => 'Registrasi Ternak', 'icon' => 'o-plus-circle'],
                        ['route' => 'weighings.create', 'label' => 'Penimbangan', 'icon' => 'o-scale'],
                        ['route' => 'movements.create', 'label' => 'Perpindahan', 'icon' => 'o-arrows-right-left'],
                        ['route' => 'births.create', 'label' => 'Kelahiran', 'icon' => 'o-sparkles'],
                        ['route' => 'deaths.create', 'label' => 'Kematian', 'icon' => 'o-exclamation-triangle'],
                        ['route' => 'feeds.index', 'label' => 'Pakan', 'icon' => 'o-inbox-stack'],
                    ] as $action)
                        <a href="{{ route($action['route']) }}"
                           class="flex items-center gap-3 bg-white px-5 py-3.5 text-sm text-neutral-700 transition-colors hover:bg-neutral-50">
                            <x-dynamic-component :component="'heroicon-'.$action['icon']" class="h-5 w-5 shrink-0 text-neutral-400" />
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card-padded">
                <h3 class="text-sm font-semibold text-neutral-800">Tentang Populasi</h3>
                <p class="mt-2 text-sm leading-relaxed text-neutral-500">
                    Populasi dihitung dari transaksi — registrasi, kelahiran, perpindahan, kematian —
                    bukan input manual. Rekonsiliasi tersedia di menu Operasional → Populasi.
                </p>

                <dl class="mt-4 space-y-2 border-t border-neutral-100 pt-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500">Sumber data</dt>
                        <dd class="font-medium text-neutral-800">Transaksi</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500">Pencatatan</dt>
                        <dd class="font-medium text-neutral-800">Otomatis</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
