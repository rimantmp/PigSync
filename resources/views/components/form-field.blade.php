@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'options' => null,
    'required' => false,
    'hint' => null,
    'span' => 1,
])

@php
    $id = $attributes->get('id', $name);
    $current = old($name, $value);
    $spanClass = $span === 2 ? 'sm:col-span-2' : '';
@endphp

@php
    // CATATAN: jangan tulis literal tag PHP atau penutup komentar Blade di
    // file ini. Blade (dan PHP di blok ini) menutup bloknya di penanda
    // penutup pertama yang ditemukan, termasuk yang ada di dalam komentar —
    // jadi contoh penulisan komentar untuk konteks lama tidak aman di sini.
    // Penjelasan panjang tentang mekanismenya lihat catatan di
    // tests/Feature/DebugModalTest.php dan riwayat git.
@endphp

<div class="{{ $spanClass }}">
    <x-input-label for="{{ $id }}" :value="$label ?? str_replace('_', ' ', ucfirst($name))" />

    <div class="mt-1.5">
        @if ($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="3" @required($required)
                      class="field-control" placeholder="{{ $hint }}">{{ $current }}</textarea>
        @elseif ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}" @required($required) class="field-control">
                <option value="">— Pilih —</option>
                @foreach ($options ?? [] as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
        @else
            <x-text-input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" :value="$current"
                          required="{{ $required ? 'required' : null }}" placeholder="{{ $hint }}" />
        @endif
    </div>

    @error($name)
        <x-input-error :messages="$message" class="mt-1.5" />
    @enderror
</div>
