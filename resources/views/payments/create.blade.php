<x-app-layout>
    <x-slot name="title">Catat Pembayaran</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="payable_type" value="Jenis" />
                    <select id="payable_type" name="payable_type" required class="mt-1 w-full rounded-md border-gray-300" x-data @change="this.form.payable_id.value=''">
                        <option value="invoice">Invoice Pembelian (hutang supplier)</option>
                        <option value="sale">Penjualan (piutang pelanggan)</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="payable_id" value="Referensi" />
                    <select id="payable_id" name="payable_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <optgroup label="Invoice">
                            @foreach ($invoices as $inv)<option value="{{ $inv->id }}">INV {{ $inv->invoice_number }} — Rp {{ number_format($inv->total,0,',','.') }}</option>@endforeach
                        </optgroup>
                        <optgroup label="Penjualan">
                            @foreach ($sales as $s)<option value="{{ $s->id }}">{{ $s->invoice_number }} — Rp {{ number_format($s->total,0,',','.') }}</option>@endforeach
                        </optgroup>
                    </select>
                </div>
                <div>
                    <x-input-label for="amount" value="Jumlah" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" required class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="method" value="Metode" />
                    <select id="method" name="method" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="tempo">Tempo</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="paid_at" value="Tanggal Bayar" />
                    <x-text-input id="paid_at" name="paid_at" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('purchase.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>