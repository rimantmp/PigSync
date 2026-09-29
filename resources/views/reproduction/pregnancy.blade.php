<x-app-layout>
    <x-slot name="title">Pemeriksaan Kebuntingan</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <div class="mb-4 text-sm text-gray-600">
            Induk: <strong>{{ $breeding->sow->code }}</strong> — kawin {{ $breeding->bred_at->format('d M Y') }}
        </div>
        <form method="POST" action="{{ route('reproduction.pregnancy.store', $breeding) }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="checked_at" value="Tanggal Periksa" />
                    <x-text-input id="checked_at" name="checked_at" type="date" :value="old('checked_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="result" value="Hasil" />
                    <select id="result" name="result" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="positif" @selected(old('result') === 'positif')>Positif (bunting)</option>
                        <option value="negatif" @selected(old('result') === 'negatif')>Negatif</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="method" value="Metode" />
                    <select id="method" name="method" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="ultrasound">Ultrasound</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('reproduction.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>