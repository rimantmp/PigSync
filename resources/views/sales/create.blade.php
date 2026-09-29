<x-app-layout>
    <x-slot name="title">Transaksi Penjualan</x-slot>

    <div class="max-w-3xl bg-white rounded-lg shadow p-6" x-data="{ rows: [{pig_id:'', weight:'', price_per_kg:''}] }">
        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="branch_id" value="Cabang" />
                    <select id="branch_id" name="branch_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach (\App\Support\BranchScope::branches(auth()->user()) as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="customer_id" value="Pelanggan" />
                    <select id="customer_id" name="customer_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih pelanggan —</option>
                        @foreach ($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="sale_date" value="Tanggal" />
                    <x-text-input id="sale_date" name="sale_date" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="payment_status" value="Status Bayar" />
                    <select id="payment_status" name="payment_status" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="belum_bayar">Belum bayar</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">Item Ternak</h3>
                    <button type="button" @click="rows.push({pig_id:'', weight:'', price_per_kg:''})" class="text-sm text-slate-600">+ Tambah baris</button>
                </div>
                <template x-for="(row, i) in rows" :key="i">
                    <div class="grid grid-cols-12 gap-2 mt-2">
                        <div class="col-span-5">
                            <select :name="'items['+i+'][pig_id]'" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">— Ternak —</option>
                                @foreach ($pigs as $p)<option value="{{ $p->id }}">{{ $p->code }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input type="number" step="0.01" :name="'items['+i+'][weight]'" placeholder="berat kg" class="w-full rounded-md border-gray-300 text-sm" inputmode="numeric" />
                        </div>
                        <div class="col-span-3">
                            <input type="number" step="0.01" :name="'items['+i+'][price_per_kg]'" placeholder="harga/kg" class="w-full rounded-md border-gray-300 text-sm" inputmode="numeric" />
                        </div>
                        <div class="col-span-1">
                            <button type="button" @click="rows.splice(i, 1)" class="text-red-500">✕</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>