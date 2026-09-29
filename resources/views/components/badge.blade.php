@props([
    'variant' => 'neutral',
    'dot' => false,
])

@php
    $styles = match ($variant) {
        'success' => 'bg-success-100 text-success-700',
        'warning' => 'bg-warning-100 text-warning-700',
        'error' => 'bg-error-100 text-error-700',
        'info' => 'bg-info-100 text-info-700',
        'primary' => 'bg-primary-100 text-primary-700',
        default => 'bg-neutral-100 text-neutral-600',
    };

    $dotColor = match ($variant) {
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
        'info' => 'bg-info-500',
        'primary' => 'bg-primary-500',
        default => 'bg-neutral-400',
    };
@endphp

{{-- §9.18 — teks + background (+ dot opsional). Warna tidak pernah sendirian. --}}
<span {{ $attributes->merge(['class' => "badge {$styles}"]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-pill {{ $dotColor }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
