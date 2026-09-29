<x-app-layout>
    <x-slot name="title">Pakan</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Stok Pakan</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">Gudang</th><th class="px-4 py-3">Cabang</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Min</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($stocks as $s)
                        <tr>
                            <td class="px-4 py-3">{{ $s->warehouse?->name }}</td>
                            <td class="px-4 py-3">{{ $s->warehouse?->branch?->name }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $s->qty }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $s->min_stock }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada stok pakan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow p-5 max-w-2xl">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Transaksi Pakan</h3>
            <form method="POST" action="{{ route('feeds.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <x-input-label for="warehouse_id" value="Gudang" />
                    <select id="warehouse_id" name="warehouse_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($stocks->pluck('warehouse')->unique('id')->filter() as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="item_id" value="Jenis Pakan" />
                    <select id="item_id" name="item_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($feedTypes as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="qty" value="Jumlah" />
                    <x-text-input id="qty" name="qty" type="number" step="0.01" required class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="direction" value="Arah" />
                    <select id="direction" name="direction" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="in">Masuk (Penerimaan)</option>
                        <option value="out">Keluar (Distribusi ke kandang)</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="pen_id" value="Kandang Tujuan (wajib bila keluar)" />
                    <select id="pen_id" name="pen_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($pens as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <x-primary-button>Proses</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>