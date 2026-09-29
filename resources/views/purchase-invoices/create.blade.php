<x-app-layout>
    <x-slot name="title">Buat Invoice Pembelian</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('purchase.invoice.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="po_id" value="Purchase Order (terima)" />
                    <select id="po_id" name="po_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih PO —</option>
                        @foreach ($orders as $o)
                            <option value="{{ $o->id }}" data-total="{{ $o->total }}">{{ $o->po_number }} — Rp {{ number_format($o->total, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="invoice_date" value="Tanggal Invoice" />
                    <x-text-input id="invoice_date" name="invoice_date" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="total" value="Total" />
                    <x-text-input id="total" name="total" type="number" step="0.01" required class="mt-1 w-full" inputmode="numeric" />
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('purchase.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>