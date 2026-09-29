<x-app-layout>
    <x-slot name="title">Pemeriksaan Kesehatan</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('health.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="pig_id" value="Ternak" />
                    <select id="pig_id" name="pig_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih ternak —</option>
                        @foreach ($pigs as $p)<option value="{{ $p->id }}" @selected(old('pig_id') == $p->id)>{{ $p->code }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="checked_at" value="Tanggal" />
                    <x-text-input id="checked_at" name="checked_at" type="date" :value="old('checked_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="symptoms" value="Gejala" />
                    <textarea id="symptoms" name="symptoms" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('symptoms') }}</textarea>
                </div>
                <div>
                    <x-input-label for="disease_id" value="Penyakit" />
                    <select id="disease_id" name="disease_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($diseases as $d)<option value="{{ $d->id }}" @selected(old('disease_id') == $d->id)>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="medicine_id" value="Obat / Vaksin" />
                    <select id="medicine_id" name="medicine_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($medicines as $m)<option value="{{ $m->id }}" @selected(old('medicine_id') == $m->id)>{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="dose" value="Dosis" />
                    <x-text-input id="dose" name="dose" :value="old('dose')" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="route" value="Cara" />
                    <select id="route" name="route" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        <option value="injeksi">Injeksi</option><option value="oral">Oral</option><option value="topikal">Topikal</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="status" value="Status ternak" />
                    <select id="status" name="status" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="sakit" @selected(old('status') === 'sakit')>Sakit</option>
                        <option value="karantina" @selected(old('status') === 'karantina')>Karantina</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('health.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>