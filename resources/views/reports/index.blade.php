<x-app-layout>
    <x-slot name="title">Laporan</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ([
            'population' => ['📊 Populasi', 'Populasi per kandang & cabang'],
            'growth' => ['📈 Pertumbuhan', 'Riwayat penimbangan & ADG'],
            'deaths' => ['⚠️ Kematian', 'Mortality & estimasi kerugian'],
            'movements' => ['🔄 Perpindahan', 'Mutasi antar kandang'],
            'health' => ['🏥 Kesehatan', 'Riwayat pemeriksaan & obat'],
        ] as $key => [$title, $desc])
            <a href="{{ route('reports.'.$key) }}" class="bg-white rounded-lg shadow p-5 hover:bg-gray-50">
                <div class="font-semibold text-gray-800">{{ $title }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ $desc }}</div>
            </a>
        @endforeach
    </div>
</x-app-layout>