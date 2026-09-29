<x-app-layout>
    <x-slot name="title">Input Penimbangan</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('weighings.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="pig_id" value="Ternak" />
                    <select id="pig_id" name="pig_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih ternak —</option>
                        @foreach ($pigs as $p)<option value="{{ $p->id }}" @selected(old('pig_id') == $p->id)>{{ $p->code }} ({{ $p->pen?->name }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="weighed_at" value="Tanggal" />
                    <x-text-input id="weighed_at" name="weighed_at" type="date" :value="old('weighed_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="weight" value="Berat (kg)" />
                    <x-text-input id="weight" name="weight" type="number" step="0.01" :value="old('weight')" required class="mt-1 w-full" inputmode="numeric" />
                    @error('weight') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="method" value="Metode" />
                    <select id="method" name="method" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="individu" @selected(old('method', 'individu') === 'individu')>Individu</option>
                        <option value="kelompok" @selected(old('method') === 'kelompok')>Kelompok</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="notes" value="Catatan" />
                <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('weighings.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>