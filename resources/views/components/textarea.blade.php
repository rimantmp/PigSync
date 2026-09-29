@props(['rows' => 3])

{{-- §9.5 — tinggi minimum 96px, resize vertikal. --}}
<textarea rows="{{ $rows }}" {{ $attributes->merge(['class' => 'field-control min-h-[96px] resize-y']) }}>
    {{ $slot }}
</textarea>
