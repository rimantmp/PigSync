<x-app-layout>
    <x-slot name="title">Pembelian</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap justify-end gap-2">
            <x-secondary-button href="{{ route('purchase-requests.index') }}">Purchase Request</x-secondary-button>
            <x-secondary-button href="{{ route('purchase.receipt.create') }}">Terima Barang</x-secondary-button>
            <x-secondary-button href="{{ route('purchase.invoice.create') }}">Buat Invoice</x-secondary-button>
            <x-secondary-button href="{{ route('payments.create') }}">Catat Pembayaran</x-secondary-button>
            <x-primary-button href="{{ route('purchase.order.create') }}">
                <x-heroicon-o-plus class="h-4 w-4" />
                Buat PO
            </x-primary-button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h3 class="px-4 pt-4 font-semibold text-gray-700">Purchase Order</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">No PO</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Supplier</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Dokumen</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $o->po_number }}</td>
                            <td class="px-4 py-3">{{ $o->po_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $o->supplier?->name }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $o->status }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('purchase.order.pdf', $o) }}" class="text-rose-600 hover:text-rose-800" title="Cetak Purchase Order">PDF</a>
                                @foreach ($o->invoices ?? [] as $inv)
                                    &middot; <a href="{{ route('purchase.invoice.pdf', $inv) }}" class="text-rose-600 hover:text-rose-800" title="Cetak Invoice">INV</a>
                                @endforeach
                                @foreach ($o->receipts ?? [] as $rec)
                                    &middot; <a href="{{ route('purchase.receipt.pdf', $rec) }}" class="text-rose-600 hover:text-rose-800" title="Cetak bukti penerimaan">GR-{{ $rec->id }}</a>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada PO.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h3 class="px-4 pt-4 font-semibold text-gray-700">Purchase Request</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Cabang</th><th class="px-4 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">PR-{{ $r->id }}</td>
                            <td class="px-4 py-3">{{ $r->request_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $r->branch?->name }}</td>
                            <td class="px-4 py-3">{{ $r->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada PR.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>