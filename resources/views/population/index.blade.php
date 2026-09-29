<x-app-layout>
    <x-slot name="title">Populasi Kandang</x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="branch_id" class="rounded-md border-gray-300 text-sm">
                <option value="">Semua cabang</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
            <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kandang..." class="w-48" />
            <x-primary-button>Filter</x-primary-button>
        </form>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Kandang</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3 text-right">Populasi</th>
                        <th class="px-4 py-3 text-right">Sakit</th>
                        <th class="px-4 py-3 text-right">Kapasitas</th>
                        <th class="px-4 py-3 w-48">Isi (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pens as $pen)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $pen->name }}</td>
                            <td class="px-4 py-3">{{ $pen->branch?->name }}</td>
                            <td class="px-4 py-3">{{ $pen->type }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $pen->population }}</td>
                            <td class="px-4 py-3 text-right text-amber-600">{{ $pen->sick }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $pen->capacity }}</td>
                            <td class="px-4 py-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $pen->fill_pct >= 100 ? 'bg-red-500' : ($pen->fill_pct >= 90 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $pen->fill_pct) }}%"></div>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $pen->fill_pct }}%</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada kandang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>