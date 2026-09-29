<x-app-layout>
    <x-slot name="title">Laporan Kesehatan</x-slot>

    @include('reports.filter')

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Diagnosis</th><th class="px-4 py-3">Obat</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($records as $r)
                    <tr><td class="px-4 py-3">{{ $r->checked_at->format('d M Y') }}</td><td class="px-4 py-3 font-medium">{{ $r->pig->code }}</td><td class="px-4 py-3">{{ $r->diagnosis ?? $r->disease?->name ?? '-' }}</td><td class="px-4 py-3">{{ $r->medicine?->name ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('reports.actions', ['report' => 'health'])
</x-app-layout>
