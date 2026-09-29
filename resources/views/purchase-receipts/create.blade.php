<x-app-layout>
    <x-slot name="title">Terima Barang (Penerimaan)</x-slot>

    <div class="max-w-3xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('purchase.receipt.store') }}" x-data="{ po_id: '', qty: {} }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="po_id" value="Purchase Order" />
                    <select id="po_id" name="po_id" required class="mt-1 w-full rounded-md border-gray-300" @change="po_id = $event.target.value">
                        <option value="">— Pilih PO —</option>
                        @foreach ($orders as $o)<option value="{{ $o->id }}">{{ $o->po_number }} ({{ $o->supplier?->name }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="received_at" value="Tanggal Terima" />
                    <x-text-input id="received_at" name="received_at" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
            </div>

            <div class="mt-5">
                <h3 class="font-semibold text-gray-700">Jumlah Diterima</h3>
                @foreach ($orders as $o)
                    <div x-show="po_id == '{{ $o->id }}'" class="mt-2 space-y-1">
                        @foreach ($o->items as $item)
                            <div class="flex items-center justify-between text-sm border-b border-gray-100 py-1">
                                <span>{{ $item->item_type }} #{{ $item->item_id }} — PO {{ $item->qty }} (diterima {{ $item->received_qty }})</span>
                                <input type="number" step="0.01" name="received_qty[{{ $item->id }}]" value="{{ $item->qty }}" class="w-24 rounded-md border-gray-300 text-sm" inputmode="numeric" />
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex gap-3">
                <x-primary-button>Terima</x-primary-button>
                <a href="{{ route('purchase.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>