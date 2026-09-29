<x-app-layout>
    <x-slot name="title">Buat Purchase Order</x-slot>

    <div class="max-w-3xl bg-white rounded-lg shadow p-6" x-data="{ rows: [{item_type:'feed', item_id:'', qty:'', price:''}] }">
        <form method="POST" action="{{ route('purchase.order.store') }}">
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
                    <x-input-label for="supplier_id" value="Supplier" />
                    <select id="supplier_id" name="supplier_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih supplier —</option>
                        @foreach ($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="po_date" value="Tanggal" />
                    <x-text-input id="po_date" name="po_date" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">Item</h3>
                    <button type="button" @click="rows.push({item_type:'feed', item_id:'', qty:'', price:''})" class="text-sm text-slate-600">+ Tambah baris</button>
                </div>
                <template x-for="(row, i) in rows" :key="i">
                    <div class="grid grid-cols-12 gap-2 mt-2">
                        <div class="col-span-3">
                            <select :name="'items['+i+'][item_type]'" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="feed">Pakan</option><option value="medicine">Obat/Vaksin</option><option value="equipment">Perlengkapan</option>
                            </select>
                        </div>
                        <div class="col-span-5">
                            <input type="number" :name="'items['+i+'][item_id]'" placeholder="item id" class="w-full rounded-md border-gray-300 text-sm" inputmode="numeric" />
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" :name="'items['+i+'][qty]'" placeholder="qty" class="w-full rounded-md border-gray-300 text-sm" inputmode="numeric" />
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" :name="'items['+i+'][price]'" placeholder="harga" class="w-full rounded-md border-gray-300 text-sm" inputmode="numeric" />
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('purchase.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>