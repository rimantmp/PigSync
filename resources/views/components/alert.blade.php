@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $styles = match ($variant) {
        'success' => ['wrap' => 'bg-success-50 border-success-200 text-success-700', 'icon' => 'text-success-500', 'name' => 'Berhasil'],
        'warning' => ['wrap' => 'bg-warning-50 border-warning-200 text-warning-700', 'icon' => 'text-warning-500', 'name' => 'Perhatian'],
        'error' => ['wrap' => 'bg-error-50 border-error-200 text-error-700', 'icon' => 'text-error-500', 'name' => 'Gagal'],
        default => ['wrap' => 'bg-info-50 border-info-200 text-info-700', 'icon' => 'text-info-500', 'name' => 'Informasi'],
    };
@endphp

{{-- §9.19 — ikon + teks, tidak pernah warna saja. --}}
<div {{ $attributes->merge(['class' => "flex gap-3 rounded-lg border px-4 py-3 text-sm {$styles['wrap']}"]) }} role="alert">
    <svg class="h-5 w-5 shrink-0 {{ $styles['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        @if ($variant === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        @elseif ($variant === 'error')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        @elseif ($variant === 'warning')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        @endif
    </svg>

    <div class="min-w-0 space-y-1">
        @if ($title)
            <p class="font-medium">{{ $title }}</p>
        @else
            <p class="sr-only">{{ $styles['name'] }}</p>
        @endif

        <div class="text-[13px] [&_ul]:list-disc [&_ul]:ms-5 [&_ul]:space-y-1">
            {{ $slot }}
        </div>
    </div>
</div>
