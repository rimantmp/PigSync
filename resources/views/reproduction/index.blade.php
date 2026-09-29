<x-app-layout>
    <x-slot name="title">Reproduksi</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end gap-2">
            <a href="{{ route('reproduction.mating') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Perkawinan</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h3 class="px-4 pt-4 font-semibold text-gray-700">Rekaman Perkawinan</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Induk</th><th class="px-4 py-3">Pejantan</th><th class="px-4 py-3">Metode</th><th class="px-4 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($breedings as $b)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $b->bred_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $b->sow->code }}</td>
                            <td class="px-4 py-3">{{ $b->boar?->code ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $b->method }}</td>
                            <td class="px-4 py-3"><a href="{{ route('reproduction.pregnancy', $b) }}" class="text-slate-600 hover:text-slate-900">Cek Kebuntingan</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada perkawinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h3 class="px-4 pt-4 font-semibold text-gray-700">Hasil Kebuntingan</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">Periksa</th><th class="px-4 py-3">Induk</th><th class="px-4 py-3">Hasil</th><th class="px-4 py-3">Est. Lahir</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pregnancies as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $p->checked_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->sow->code }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $p->result === 'positif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $p->result }}</span></td>
                            <td class="px-4 py-3">{{ $p->expected_farrow_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada hasil pemeriksaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>