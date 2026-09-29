{{-- Master index generik --}}
<x-app-layout>
    <x-slot name="title">{{ $label }}</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex-1 max-w-sm">
                <form method="GET" class="flex gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode / nama..." class="w-full" />
                    <x-primary-button>Cari</x-primary-button>
                </form>
            </div>
            <a href="{{ route($prefix.'.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">
                + Tambah {{ $label }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">#</th>
                        @foreach ($columns as $col)
                            @php $def = $definitions[$col] ?? ['label' => $col]; @endphp
                            <th class="px-4 py-3 font-medium">{{ $def['label'] }}</th>
                        @endforeach
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400">{{ $row->id }}</td>
                            @foreach ($columns as $col)
                                @if ($col === 'branch_id')
                                    <td class="px-4 py-3">{{ $row->branch?->name ?? '-' }}</td>
                                @elseif ($col === 'area_id')
                                    <td class="px-4 py-3">{{ $row->area?->name ?? '-' }}</td>
                                @elseif ($col === 'unit_id')
                                    <td class="px-4 py-3">{{ $row->unit?->name ?? '-' }}</td>
                                @elseif (isset($definitions[$col]) && $definitions[$col]['type'] === 'select' && is_numeric($row->{$col}))
                                    <td class="px-4 py-3">{{ $row->{$col} == 1 ? 'Ya' : 'Tidak' }}</td>
                                @else
                                    <td class="px-4 py-3">{{ $row->{$col} ?? '-' }}</td>
                                @endif
                            @endforeach
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route($prefix.'.edit', $row) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route($prefix.'.destroy', $row) }}" class="inline ms-3" onsubmit="return confirm('Hapus {{ $label }} ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) + 2 }}" class="px-4 py-8 text-center text-gray-400">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</x-app-layout>