{{--
    Dashboard. Chart dibangun dari CSS (bar + conic-gradient), tanpa library
    eksternal, supaya warna tetap memakai token design system.
--}}
@php
    $phasePalette = ['#F97316', '#FB923C', '#FDBA74', '#FED7AA', '#FFEDD5', '#E5E7EB'];
    $phaseTotal = max(1, array_sum(array_column($byPhase, 'jumlah')));

    // Offset gradien donat, kumulatif dari tiap fase.
    $offset = 0;
    $segments = [];
    foreach ($byPhase as $i => $phase) {
        $size = round($phase['jumlah'] / $phaseTotal * 100, 2);
        $segments[] = $phasePalette[$i % count($phasePalette)].' '.round($offset, 2).'% '.round($offset + $size, 2).'%';
        $offset += $size;
    }
    $donut = $segments ? 'conic-gradient('.implode(', ', $segments).')' : '#E5E7EB';

    $trendMax = $weightTrend ? max(array_column($weightTrend, 'rerata')) : 0;
    $trendMin = $weightTrend ? min(array_column($weightTrend, 'rerata')) : 0;
    // Bar diskalakan ke rentang data, bukan ke nilai absolut, supaya tren
    // terlihat walau semua nilai nominally besar.
    $trendRange = max(1, $trendMax - $trendMin);

    $first = $weightTrend[0] ?? null;
    $last = $weightTrend[array_key_last($weightTrend)] ?? null;
    $adg = ($first && $last && $first !== $last)
        ? round(
            ($last['rerata'] - $first['rerata'])
            / max(1, \Illuminate\Support\Carbon::parse($last['tanggal'])->diffInDays(\Illuminate\Support\Carbon::parse($first['tanggal']))),
            2
        )
        : null;
@endphp

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-neutral-900">Ringkasan Peternakan</h2>
                <p class="mt-0.5 text-sm text-neutral-500">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>

            <x-primary-button href="{{ route('pigs.create') }}">
                <x-heroicon-o-plus class="h-4 w-4" />
                Registrasi Ternak
            </x-primary-button>
        </div>

        {{-- Peringatan: hanya muncul kalau ada yang perlu tindakan --}}
        @if (count($summary['alerts']) > 0)
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($summary['alerts'] as $alert)
                    @php
                        $tone = match ($alert['tone']) {
                            'error' => ['bg-error-50 border-error-200', 'text-error-600', 'o-exclamation-triangle'],
                            'warning' => ['bg-warning-50 border-warning-200', 'text-warning-600', 'o-exclamation-circle'],
                            default => ['bg-info-50 border-info-200', 'text-info-600', 'o-information-circle'],
                        };
                    @endphp

                    <a href="{{ $alert['link'] }}"
                       class="flex gap-3 rounded-lg border p-4 transition-opacity hover:opacity-80 {{ $tone[0] }}">
                        <x-dynamic-component :component="'heroicon-'.$tone[2]" class="h-5 w-5 shrink-0 {{ $tone[1] }}" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-neutral-800">{{ $alert['title'] }}</p>
                            <p class="mt-0.5 text-[13px] text-neutral-600">{{ $alert['detail'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- KPI utama --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Populasi', 'value' => number_format($summary['totalPopulasi']), 'unit' => 'ekor hidup', 'tone' => 'text-neutral-900', 'icon' => 'o-cube', 'link' => 'population.index'],
                ['label' => 'Sakit / Karantina', 'value' => number_format($summary['sakit']), 'unit' => 'ekor perlu perhatian', 'tone' => $summary['sakit'] > 0 ? 'text-warning-600' : 'text-neutral-900', 'icon' => 'o-beaker', 'link' => 'health.index'],
                ['label' => 'Kematian Bulan Ini', 'value' => number_format($summary['matiBulanIni']), 'unit' => 'total kematian '.$summary['mortalityRate'].'%', 'tone' => $summary['matiBulanIni'] > 0 ? 'text-error-600' : 'text-neutral-900', 'icon' => 'o-heart', 'link' => 'reports.deaths'],
                ['label' => 'Kelahiran Bulan Ini', 'value' => number_format($summary['lahirBulanIni']), 'unit' => 'piglet hidup', 'tone' => 'text-success-600', 'icon' => 'o-sparkles', 'link' => 'births.index'],
            ] as $card)
                <a href="{{ route($card['link']) }}" class="card-padded transition-colors hover:border-neutral-300">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-neutral-500">{{ $card['label'] }}</p>
                        <x-dynamic-component :component="'heroicon-'.$card['icon']" class="h-5 w-5 shrink-0 text-neutral-300" />
                    </div>
                    <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight {{ $card['tone'] }}">
                        {{ $card['value'] }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-400">{{ $card['unit'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Tren berat --}}
            <div class="card lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-100 px-5 py-3.5">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-800">Tren Berat Rata-rata</h3>
                        <p class="text-xs text-neutral-500">Kilogram, seluruh ternak dalam scope</p>
                    </div>
                    @if ($adg !== null)
                        <x-badge variant="{{ $adg >= 0 ? 'success' : 'error' }}" dot>
                            ADG {{ $adg > 0 ? '+' : '' }}{{ $adg }} kg/hari
                        </x-badge>
                    @endif
                </div>

                @if (count($weightTrend) < 2)
                    <x-empty-state
                        title="Belum ada tren"
                        description="Butuh minimal dua kali penimbangan untuk melihat tren berat." />
                @else
                    <div class="p-5">
                        <div class="flex h-40 items-end gap-3">
                            @foreach ($weightTrend as $point)
                                @php
                                    $height = 25 + round(($point['rerata'] - $trendMin) / $trendRange * 75);
                                @endphp
                                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                                    <span class="text-xs font-medium tabular-nums text-neutral-600">{{ $point['rerata'] }}</span>
                                    <div class="w-full rounded-t {{ $loop->last ? 'bg-primary-500' : 'bg-primary-200' }}"
                                         style="height: {{ $height }}%"
                                         title="{{ $point['label'] }} — {{ $point['jumlah'] }} ekor"></div>
                                    <span class="text-[11px] text-neutral-500">{{ $point['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Distribusi fase --}}
            <div class="card">
                <div class="border-b border-neutral-100 px-5 py-3.5">
                    <h3 class="text-sm font-semibold text-neutral-800">Distribusi Fase</h3>
                    <p class="text-xs text-neutral-500">Komposisi populasi hidup</p>
                </div>

                @if ($byPhase === [])
                    <x-empty-state title="Belum ada data" description="Belum ada ternak hidup untuk ditampilkan." />
                @else
                    <div class="p-5">
                        <div class="flex items-center gap-5">
                            <div class="relative h-28 w-28 shrink-0 rounded-pill" style="background: {{ $donut }}">
                                <div class="absolute inset-[14px] flex flex-col items-center justify-center rounded-pill bg-white">
                                    <span class="text-lg font-semibold tabular-nums text-neutral-900">{{ $phaseTotal }}</span>
                                    <span class="text-[10px] text-neutral-500">ekor</span>
                                </div>
                            </div>

                            <ul class="min-w-0 flex-1 space-y-1.5">
                                @foreach ($byPhase as $i => $phase)
                                    <li class="flex items-center gap-2 text-[13px]">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-pill"
                                              style="background: {{ $phasePalette[$i % count($phasePalette)] }}"></span>
                                        <span class="min-w-0 flex-1 truncate text-neutral-600">{{ $phase['name'] }}</span>
                                        <span class="font-medium tabular-nums text-neutral-800">{{ $phase['jumlah'] }}</span>
                                        <span class="w-9 text-right tabular-nums text-neutral-400">
                                            {{ round($phase['jumlah'] / $phaseTotal * 100) }}%
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Kapasitas kandang --}}
            <div class="card lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-100 px-5 py-3.5">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-800">Pengisian Kandang</h3>
                        <p class="text-xs text-neutral-500">Total kapasitas {{ number_format($summary['kapasitas']) }} ekor</p>
                    </div>
                    <x-badge variant="{{ $summary['fillRate'] >= 90 ? 'warning' : 'neutral' }}" dot>
                        {{ $summary['fillRate'] }}% terisi
                    </x-badge>
                </div>

                @if ($byPen === [])
                    <x-empty-state title="Belum ada kandang" description="Belum ada kandang di cabang Anda." />
                @else
                    <ul class="divide-y divide-neutral-100">
                        @foreach ($byPen as $pen)
                            @php
                                $tone = $pen['fill'] >= 90 ? 'bg-error-500' : ($pen['fill'] >= 70 ? 'bg-warning-500' : 'bg-primary-500');
                            @endphp
                            <li class="px-5 py-3">
                                <div class="flex items-baseline justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-neutral-800">{{ $pen['name'] }}</p>
                                        <p class="truncate text-xs text-neutral-500">{{ $pen['cabang'] }}</p>
                                    </div>
                                    <p class="shrink-0 text-sm tabular-nums text-neutral-600">
                                        <span class="font-semibold text-neutral-900">{{ $pen['populasi'] }}</span>
                                        <span class="text-neutral-400"> / {{ $pen['kapasitas'] }}</span>
                                    </p>
                                </div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-pill bg-neutral-100">
                                    <div class="h-full rounded-pill {{ $tone }}" style="width: {{ max(2, $pen['fill']) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="space-y-4">
                {{-- Keuangan --}}
                <div class="card-padded">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-800">Keuangan Bulan Ini</h3>
                        <a href="{{ route('finance.index') }}" class="text-xs text-neutral-500 hover:text-neutral-800">Detail</a>
                    </div>

                    <dl class="mt-3 space-y-2.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-neutral-500">Pendapatan</dt>
                            <dd class="font-medium tabular-nums text-success-600">+{{ rupiah($finance['pendapatan']) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-neutral-500">Biaya</dt>
                            <dd class="font-medium tabular-nums text-error-600">−{{ rupiah($finance['biaya']) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-neutral-100 pt-2.5">
                            <dt class="font-medium text-neutral-700">Selisih</dt>
                            <dd class="font-semibold tabular-nums {{ $finance['profit'] >= 0 ? 'text-success-600' : 'text-error-600' }}">
                                {{ rupiah($finance['profit']) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Penjualan --}}
                <div class="card-padded">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-800">Penjualan</h3>
                        <a href="{{ route('sales.index') }}" class="text-xs text-neutral-500 hover:text-neutral-800">Detail</a>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-success-50 p-3">
                            <p class="text-[11px] text-success-700">Lunas</p>
                            <p class="mt-0.5 text-base font-semibold tabular-nums text-success-700">{{ $sales['lunas'] }}</p>
                            <p class="text-[11px] tabular-nums text-success-600">{{ rupiah($sales['lunas_nominal']) }}</p>
                        </div>
                        <div class="rounded-lg bg-warning-50 p-3">
                            <p class="text-[11px] text-warning-700">Belum bayar</p>
                            <p class="mt-0.5 text-base font-semibold tabular-nums text-warning-700">{{ $sales['belum_bayar'] }}</p>
                            <p class="text-[11px] tabular-nums text-warning-600">{{ rupiah($sales['belum_bayar_nominal']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Aktivitas terbaru --}}
            <div class="card">
                <div class="border-b border-neutral-100 px-5 py-3.5">
                    <h3 class="text-sm font-semibold text-neutral-800">Aktivitas Terbaru</h3>
                    <p class="text-xs text-neutral-500">Transaksi terakhir yang tercatat</p>
                </div>

                @if ($activity->isEmpty())
                    <x-empty-state title="Belum ada aktivitas" description="Transaksi yang tercatat akan muncul di sini." />
                @else
                    <ul class="divide-y divide-neutral-100">
                        @foreach ($activity as $log)
                            <li class="flex items-start gap-3 px-5 py-3">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-pill {{ $log->action === 'create' ? 'bg-success-500' : ($log->action === 'update' ? 'bg-info-500' : 'bg-neutral-300') }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] text-neutral-700">
                                        <span class="font-medium text-neutral-900">{{ $log->user?->name ?? 'Sistem' }}</span>
                                        {{ $log->action === 'create' ? 'menambahkan' : ($log->action === 'update' ? 'memperbarui' : 'mengubah') }}
                                        <span class="font-medium">{{ $log->module }}</span>
                                    </p>
                                    <p class="text-xs text-neutral-400">
                                        @if ($log->entity_type)
                                            {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}
                                        @endif
                                        @if ($log->at)&middot; {{ $log->at->diffForHumans() }} @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Aksi cepat --}}
            <div class="card">
                <div class="border-b border-neutral-100 px-5 py-3.5">
                    <h3 class="text-sm font-semibold text-neutral-800">Aksi Cepat</h3>
                    <p class="text-xs text-neutral-500">Yang paling sering dipakai</p>
                </div>

                <div class="grid grid-cols-1 gap-px bg-neutral-100 sm:grid-cols-2">
                    @foreach ([
                        ['route' => 'pigs.create', 'label' => 'Registrasi Ternak', 'icon' => 'o-plus-circle'],
                        ['route' => 'weighings.create', 'label' => 'Penimbangan', 'icon' => 'o-scale'],
                        ['route' => 'movements.create', 'label' => 'Perpindahan', 'icon' => 'o-arrows-right-left'],
                        ['route' => 'health.create', 'label' => 'Pemeriksaan', 'icon' => 'o-beaker'],
                        ['route' => 'births.create', 'label' => 'Kelahiran', 'icon' => 'o-sparkles'],
                        ['route' => 'deaths.create', 'label' => 'Kematian', 'icon' => 'o-exclamation-triangle'],
                        ['route' => 'purchase.order.create', 'label' => 'Buat PO', 'icon' => 'o-shopping-cart'],
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
        </div>
    </div>
</x-app-layout>
