<x-app-layout>
    <x-slot name="title">Kelahiran</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('births.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Catat Kelahiran</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Induk</th><th class="px-4 py-3">Kandang</th>
                        <th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Hidup</th><th class="px-4 py-3 text-right">Mati</th><th class="px-4 py-3 text-right">Mummy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($births as $b)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $b->farrowed_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $b->sow?->code ?? 'induk_tidak_diketahui' }}</td>
                            <td class="px-4 py-3">{{ $b->pen?->name }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $b->total_born }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600">{{ $b->born_alive }}</td>
                            <td class="px-4 py-3 text-right text-red-600">{{ $b->born_dead }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $b->mummified }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada kelahiran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $births->links() }}
    </div>
</x-app-layout>