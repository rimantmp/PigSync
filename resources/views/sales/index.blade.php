<x-app-layout>
    <x-slot name="title">Penjualan</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <x-primary-button href="{{ route('sales.create') }}">Transaksi Penjualan</x-primary-button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sales as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $s->invoice_number }}</td>
                            <td class="px-4 py-3">{{ $s->sale_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $s->customer?->name }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $s->payment_status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $s->payment_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sales->links() }}
    </div>
</x-app-layout>