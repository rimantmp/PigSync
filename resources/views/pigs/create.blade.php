<x-app-layout>
    <x-slot name="title">Registrasi Ternak</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('pigs.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sex" value="Jenis Kelamin" />
                    <select id="sex" name="sex" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="jantan" @selected(old('sex') === 'jantan')>Jantan</option>
                        <option value="betina" @selected(old('sex') === 'betina')>Betina</option>
                    </select>
                    @error('sex') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="breed_id" value="Ras / Jenis" />
                    <select id="breed_id" name="breed_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($breeds as $b)
                            <option value="{{ $b->id }}" @selected(old('breed_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="birth_date" value="Tanggal Lahir" />
                    <x-text-input id="birth_date" name="birth_date" type="date" :value="old('birth_date', now()->toDateString())" required class="mt-1 w-full" />
                    @error('birth_date') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="origin_type" value="Asal" />
                    <select id="origin_type" name="origin_type" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="internal" @selected(old('origin_type') === 'internal')>Lahir di farm</option>
                        <option value="eksternal" @selected(old('origin_type') === 'eksternal')>Beli dari luar</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="pen_id" value="Kandang Penempatan" />
                    <select id="pen_id" name="pen_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih kandang —</option>
                        @foreach ($pens as $p)
                            <option value="{{ $p->id }}" @selected(old('pen_id') == $p->id)>{{ $p->name }} ({{ $p->branch?->name }})</option>
                        @endforeach
                    </select>
                    @error('pen_id') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-input-label for="phase_id" value="Fase (opsional, auto dari usia)" />
                    <select id="phase_id" name="phase_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Auto</option>
                        @foreach ($phases as $ph)
                            <option value="{{ $ph->id }}" @selected(old('phase_id') == $ph->id)>{{ $ph->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="initial_weight" value="Berat Awal (kg)" />
                    <x-text-input id="initial_weight" name="initial_weight" type="number" step="0.01" :value="old('initial_weight')" class="mt-1 w-full" inputmode="numeric" />
                </div>
                <div>
                    <x-input-label for="tag_id" value="Ear Tag (opsional)" />
                    <x-text-input id="tag_id" name="tag_id" :value="old('tag_id')" class="mt-1 w-full" />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="notes" value="Catatan" />
                <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-6 flex gap-3">
                <x-primary-button>Daftarkan</x-primary-button>
                <a href="{{ route('pigs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>