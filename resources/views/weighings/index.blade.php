<x-app-layout>
    <x-slot name="title">Penimbangan</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <form method="GET" class="flex gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode ternak..." class="w-52" />
                <x-primary-button>Filter</x-primary-button>
            </form>
            <a href="{{ route('weighings.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Timbang</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Ternak</th><th class="px-4 py-3">Kandang</th>
                        <th class="px-4 py-3 text-right">Berat (kg)</th><th class="px-4 py-3">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($weights as $w)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $w->weighed_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $w->pig->code }}</td>
                            <td class="px-4 py-3">{{ $w->pig->pen?->name }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $w->weight }}</td>
                            <td class="px-4 py-3">{{ $w->method }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada penimbangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $weights->links() }}
    </div>
</x-app-layout>