<x-app-layout>
    <x-slot name="title">Edit {{ $pig->code }}</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('pigs.update', $pig) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sex" value="Jenis Kelamin" />
                    <select id="sex" name="sex" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="jantan" @selected($pig->sex === 'jantan')>Jantan</option>
                        <option value="betina" @selected($pig->sex === 'betina')>Betina</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="breed_id" value="Ras" />
                    <select id="breed_id" name="breed_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($breeds as $b)<option value="{{ $b->id }}" @selected($pig->breed_id == $b->id)>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="phase_id" value="Fase" />
                    <select id="phase_id" name="phase_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">—</option>
                        @foreach ($phases as $ph)<option value="{{ $ph->id }}" @selected($pig->phase_id == $ph->id)>{{ $ph->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="tag_id" value="Ear Tag" />
                    <x-text-input id="tag_id" name="tag_id" :value="$pig->tag_id" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="rfid" value="RFID" />
                    <x-text-input id="rfid" name="rfid" :value="$pig->rfid" class="mt-1 w-full" />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="notes" value="Catatan" />
                <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ $pig->notes }}</textarea>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('pigs.show', $pig) }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>