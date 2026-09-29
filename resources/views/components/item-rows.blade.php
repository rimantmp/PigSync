{{--
    Baris item dinamis (PO & Purchase Request).

    item_id memakai select dari master item sesuai item_type, bukan input
    angka bebas — sebelumnya user bisa mengetik id yang tidak ada dan sistem
    tetap menyimpannya.
--}}
@props([
    'name' => 'items',
    'withPrice' => true,
])

@php
    $catalogOptions = collect(\App\Support\ItemCatalog::options())
        ->map(fn ($items) => $items->map(fn ($label, $id) => ['id' => $id, 'name' => $label])->values())
        ->all();
@endphp

<div class="mt-5" x-data="itemRows(@js($catalogOptions))">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Item</h3>
        <button type="button" @click="add()" class="text-sm text-slate-600">+ Tambah baris</button>
    </div>

    <template x-for="(row, i) in rows" :key="i">
        <div class="grid grid-cols-12 gap-2 mt-2">
            <div class="col-span-3">
                <select :name="'{{ $name }}['+i+'][item_type]'" x-model="row.item_type" class="w-full rounded-md border-gray-300 text-sm">
                    @foreach (\App\Support\ItemCatalog::types() as $type)
                        <option value="{{ $type }}">{{ \App\Support\ItemCatalog::labelFor($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-5">
                <select :name="'{{ $name }}['+i+'][item_id]'" x-model="row.item_id" class="w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">— pilih item —</option>
                    <template x-for="item in (options[row.item_type] || [])" :key="item.id">
                        <option :value="item.id" x-text="item.name"></option>
                    </template>
                </select>
            </div>
            <div class="col-span-2">
                <input type="number" step="0.01" min="0" :name="'{{ $name }}['+i+'][qty]'" x-model="row.qty" placeholder="qty" class="w-full rounded-md border-gray-300 text-sm" required />
            </div>
            @if ($withPrice)
                <div class="col-span-2">
                    <input type="number" step="0.01" min="0" :name="'{{ $name }}['+i+'][price]'" x-model="row.price" placeholder="harga" class="w-full rounded-md border-gray-300 text-sm" required />
                </div>
            @endif
        </div>
    </template>
</div>
