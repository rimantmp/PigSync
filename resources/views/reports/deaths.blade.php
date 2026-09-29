<x-app-layout>
    <x-slot name="title">Laporan Kematian</x-slot>

    @include('reports.filter')

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Penyebab</th><th class="px-4 py-3 text-right">Estimasi Kerugian</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($deaths as $d)
                    <tr><td class="px-4 py-3">{{ $d->died_at->format('d M Y') }}</td><td class="px-4 py-3 font-medium">{{ $d->pig->code }}</td><td class="px-4 py-3">{{ $d->cause }}</td><td class="px-4 py-3 text-right">Rp {{ number_format($d->estimated_loss ?? 0, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('reports.actions', ['report' => 'deaths'])
</x-app-layout>
