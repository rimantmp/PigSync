<x-app-layout>
    <x-slot name="title">Catat Kematian</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('deaths.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="pig_id" value="Ternak" />
                    <select id="pig_id" name="pig_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih ternak —</option>
                        @foreach ($pigs as $p)<option value="{{ $p->id }}" @selected(old('pig_id') == $p->id)>{{ $p->code }}</option>@endforeach
                    </select>
                    @error('pig_id') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="died_at" value="Tanggal" />
                    <x-text-input id="died_at" name="died_at" type="date" :value="old('died_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="cause" value="Penyebab (wajib)" />
                    <select id="cause" name="cause" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        <option value="penyakit">Penyakit</option><option value="karnivor">Karnivor</option>
                        <option value="kecelakaan">Kecelakaan</option><option value="bantai_afkir">Bantai afkir</option><option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="suspected_disease_id" value="Dugaan Penyakit" />
                    <select id="suspected_disease_id" name="suspected_disease_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($diseases as $d)<option value="{{ $d->id }}" @selected(old('suspected_disease_id') == $d->id)>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="disposal" value="Tindakan" />
                    <select id="disposal" name="disposal" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        <option value="kubur">Kubur</option><option value="bakar">Bakar</option><option value="afkir_jual">Afkir jual</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="notes" value="Catatan" />
                <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('deaths.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>