@props([
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[40px] rounded-lg border text-sm font-medium transition-colors disabled:opacity-50 disabled:pointer-events-none';
    $variant = 'bg-white border-neutral-300 text-neutral-700 hover:bg-neutral-50 active:bg-neutral-100';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $variant"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $variant"]) }}>
        {{ $slot }}
    </button>
@endif
