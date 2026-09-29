{{-- Master form generik --}}
<x-app-layout>
    <x-slot name="title">{{ isset($row) && $row->id ? 'Edit' : 'Tambah' }} {{ $label }}</x-slot>

    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($row) && $row->id ? route($prefix.'.update', $row) : route($prefix.'.store') }}">
            @csrf
            @if (isset($row) && $row->id) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-4">
                @foreach ($fields as $name => $def)
                    <div>
                        <x-input-label for="{{ $name }}" :value="$def['label']" />
                        @php
                            $type = $def['type'] ?? 'text';
                            $value = old($name, $row->{$name} ?? null);
                        @endphp

                        @if ($type === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ $value }}</textarea>
                        @elseif ($type === 'select')
                            <select id="{{ $name }}" name="{{ $name }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">— Pilih —</option>
                                @foreach (fieldOptions($def['options'] ?? [], $name) as $val => $txt)
                                    <option value="{{ $val }}" @selected($value == $val)>{{ $txt }}</option>
                                @endforeach
                            </select>
                        @else
                            <x-text-input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" :value="$value" class="mt-1 w-full" />
                        @endif
                        @error($name) <x-input-error :messages="$message" class="mt-1" /> @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route($prefix.'.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>