<x-app-layout>
    <x-slot name="title">Data Babi</x-slot>

    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode / ear tag..." class="w-56" />
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="pen_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua kandang</option>
                    @foreach ($pens as $p)
                        <option value="{{ $p->id }}" @selected(request('pen_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                <x-primary-button>Filter</x-primary-button>
            </form>
            <a href="{{ route('pigs.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Registrasi Ternak</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Tag</th>
                        <th class="px-4 py-3">Kelamin</th>
                        <th class="px-4 py-3">Ras</th>
                        <th class="px-4 py-3">Kandang</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pigs as $pig)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a href="{{ route('pigs.show', $pig) }}" class="hover:underline">{{ $pig->code }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $pig->tag_id ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $pig->sex }}</td>
                            <td class="px-4 py-3">{{ $pig->breed?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $pig->pen?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $pig->pen?->branch?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ statusBadge($pig->status) }}">{{ $pig->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('pigs.show', $pig) }}" class="text-slate-600 hover:text-slate-900">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada ternak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pigs->links() }}
    </div>
</x-app-layout>