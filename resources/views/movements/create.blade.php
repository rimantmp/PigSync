<x-app-layout>
    <x-slot name="title">Perpindahan Ternak</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('movements.store') }}">
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
                    <x-input-label for="to_pen_id" value="Kandang Tujuan" />
                    <select id="to_pen_id" name="to_pen_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih kandang —</option>
                        @foreach ($pens as $p)<option value="{{ $p->id }}" @selected(old('to_pen_id') == $p->id)>{{ $p->name }} ({{ $p->branch?->name }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="moved_at" value="Tanggal" />
                    <x-text-input id="moved_at" name="moved_at" type="date" :value="old('moved_at', now()->toDateString())" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="reason" value="Alasan (wajib)" />
                    <x-text-input id="reason" name="reason" :value="old('reason')" required class="mt-1 w-full" />
                    @error('reason') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="notes" value="Catatan" />
                <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
            </div>
            <div class="mt-6 flex gap-3">
                <x-primary-button>Pindahkan</x-primary-button>
                <a href="{{ route('movements.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>