<x-app-layout>
    <x-slot name="title">Laporan Perpindahan</x-slot>

    @include('reports.filter')

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Dari</th><th class="px-4 py-3">Ke</th><th class="px-4 py-3">Alasan</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movements as $m)
                    <tr><td class="px-4 py-3">{{ $m->moved_at->format('d M Y') }}</td><td class="px-4 py-3 font-medium">{{ $m->pig->code }}</td><td class="px-4 py-3">{{ $m->fromPen?->name ?? '—' }}</td><td class="px-4 py-3">{{ $m->toPen?->name }}</td><td class="px-4 py-3">{{ $m->reason }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('reports.actions', ['report' => 'movements'])
</x-app-layout>
