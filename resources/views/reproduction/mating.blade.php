<x-app-layout>
    <x-slot name="title">Perkawinan</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('reproduction.mating.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sow_id" value="Induk (Betina)" />
                    <select id="sow_id" name="sow_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih induk —</option>
                        @foreach ($sows as $s)<option value="{{ $s->id }}" @selected(old('sow_id') == $s->id)>{{ $s->code }}</option>@endforeach
                    </select>
                    @error('sow_id') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="boar_id" value="Pejantan (Jantan)" />
                    <select id="boar_id" name="boar_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih pejantan —</option>
                        @foreach ($boars as $b)<option value="{{ $b->id }}" @selected(old('boar_id') == $b->id)>{{ $b->code }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="bred_at" value="Tanggal Kawin" />
                    <x-text-input id="bred_at" name="bred_at" type="date" :value="old('bred_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="method" value="Metode" />
                    <select id="method" name="method" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="alami" @selected(old('method') === 'alami')>Alami</option>
                        <option value="inseminasi" @selected(old('method') === 'inseminasi')>Inseminasi (AI)</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="technician" value="Teknisi" />
                    <x-text-input id="technician" name="technician" :value="old('technician')" class="mt-1 w-full" />
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('reproduction.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>