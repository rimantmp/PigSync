<x-app-layout>
    <x-slot name="title">Perpindahan Ternak</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <x-primary-button href="{{ route('movements.create') }}">Pindahkan</x-primary-button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th>
                        <th class="px-4 py-3">Dari</th><th class="px-4 py-3">Ke</th><th class="px-4 py-3">Alasan</th><th class="px-4 py-3">Tipe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $m->moved_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $m->pig->code }}</td>
                            <td class="px-4 py-3">{{ $m->fromPen?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $m->toPen?->name }}</td>
                            <td class="px-4 py-3">{{ $m->reason }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $m->type === 'cross_branch' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">{{ $m->type }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada perpindahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $movements->links() }}
    </div>
</x-app-layout>