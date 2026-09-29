<x-app-layout>
    <x-slot name="title">Kesehatan</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('health.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Pemeriksaan</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Diagnosis</th>
                        <th class="px-4 py-3">Obat</th><th class="px-4 py-3">Withdrawal s.d.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $r->checked_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r->pig->code }}</td>
                            <td class="px-4 py-3">{{ $r->diagnosis ?? $r->disease?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->medicine?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->withdrawal_until?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada pemeriksaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    </div>
</x-app-layout>