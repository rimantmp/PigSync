<x-app-layout>
    <x-slot name="title">Laporan Populasi</x-slot>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr><th class="px-4 py-3">Kandang</th><th class="px-4 py-3">Cabang</th><th class="px-4 py-3 text-right">Populasi</th><th class="px-4 py-3 text-right">Kapasitas</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pens as $pen)
                    <tr><td class="px-4 py-3 font-medium">{{ $pen->name }}</td><td class="px-4 py-3">{{ $pen->branch?->name }}</td><td class="px-4 py-3 text-right font-bold">{{ $pen->population }}</td><td class="px-4 py-3 text-right">{{ $pen->capacity }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <a href="{{ route('reports.index') }}" class="inline-block mt-3 text-sm text-slate-600">← Kembali</a>
</x-app-layout>