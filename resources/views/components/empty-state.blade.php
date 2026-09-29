@props([
    'title' => 'Belum ada data',
    'description' => null,
])

{{-- §9.24 — menjelaskan apa yang kosong, mengapa, dan apa yang bisa dilakukan. --}}
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="flex h-11 w-11 items-center justify-center rounded-pill bg-neutral-100 text-neutral-400" aria-hidden="true">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-3l-2 3h-6l-2-3H4" />
        </svg>
    </div>

    <p class="text-sm font-medium text-neutral-700">{{ $title }}</p>

    @if ($description)
        <p class="max-w-sm text-[13px] text-neutral-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
