<x-app-layout>
    <x-slot name="title">Pembelian</x-slot>

    <div class="space-y-6">
        <div class="flex justify-end">
            <a href="{{ route('purchase.order.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-700">+ Buat Purchase Order</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h3 class="px-4 pt-4 font-semibold text-gray-700">Purchase Order</h3>
            <table class="min-w-full text-sm mt-2">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">No PO</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Supplier</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $o->po_number }}</td>
                            <td class="px-4 py-3">{{ $o->po_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $o->supplier?->name }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $o->status }}</td>
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