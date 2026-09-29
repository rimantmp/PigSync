@props([
    'href' => null,
    'type' => 'submit',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[40px] rounded-lg border text-sm font-medium transition-colors disabled:opacity-50 disabled:pointer-events-none';
    $variant = 'bg-primary-500 border-transparent text-white hover:bg-primary-600 active:bg-primary-700';
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
