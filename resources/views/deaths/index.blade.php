<x-app-layout>
    <x-slot name="title">Kematian</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('deaths.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Catat Kematian</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Penyebab</th>
                        <th class="px-4 py-3">Disposal</th><th class="px-4 py-3 text-right">Estimasi Kerugian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($deaths as $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $d->died_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $d->pig->code }}</td>
                            <td class="px-4 py-3">{{ $d->cause }}</td>
                            <td class="px-4 py-3">{{ $d->disposal ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($d->estimated_loss ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada kematian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $deaths->links() }}
    </div>
</x-app-layout>