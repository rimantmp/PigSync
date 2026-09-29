<x-app-layout>
    <x-slot name="title">Laporan Pertumbuhan</x-slot>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Kandang</th><th class="px-4 py-3 text-right">Berat</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($weights as $w)
                    <tr><td class="px-4 py-3">{{ $w->weighed_at->format('d M Y') }}</td><td class="px-4 py-3 font-medium">{{ $w->pig->code }}</td><td class="px-4 py-3">{{ $w->pig->pen?->name }}</td><td class="px-4 py-3 text-right">{{ $w->weight }} kg</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <a href="{{ route('reports.index') }}" class="inline-block mt-3 text-sm text-slate-600">← Kembali</a>
</x-app-layout>