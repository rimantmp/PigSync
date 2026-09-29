<x-app-layout>
    <x-slot name="title">Catat Kelahiran</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('births.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sow_id" value="Induk (kosongkan bila tidak diketahui)" />
                    <select id="sow_id" name="sow_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Tidak diketahui —</option>
                        @foreach ($sows as $s)<option value="{{ $s->id }}" @selected(old('sow_id') == $s->id)>{{ $s->code }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="pen_id" value="Kandang" />
                    <select id="pen_id" name="pen_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih kandang —</option>
                        @foreach ($pens as $p)<option value="{{ $p->id }}" @selected(old('pen_id') == $p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="farrowed_at" value="Tanggal" />
                    <x-text-input id="farrowed_at" name="farrowed_at" type="date" :value="old('farrowed_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="born_alive" value="Lahir Hidup" />
                    <x-text-input id="born_alive" name="born_alive" type="number" :value="old('born_alive', 0)" required class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="born_dead" value="Lahir Mati" />
                    <x-text-input id="born_dead" name="born_dead" type="number" :value="old('born_dead', 0)" class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="mummified" value="Mummy" />
                    <x-text-input id="mummified" name="mummified" type="number" :value="old('mummified', 0)" class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="avg_weight" value="Berat rata-rata (kg)" />
                    <x-text-input id="avg_weight" name="avg_weight" type="number" step="0.01" :value="old('avg_weight')" class="mt-1 w-full" inputmode="numeric" />
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('births.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>