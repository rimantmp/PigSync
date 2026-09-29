@php
    if (isset($active)) {
        $classes = $active
            ? 'block w-full ps-3 pe-4 py-2 border-s-4 border-primary-500 text-start text-base font-medium text-primary-700 bg-primary-50 transition-colors'
            : 'block w-full ps-3 pe-4 py-2 border-s-4 border-transparent text-start text-base font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 transition-colors';
    } else {
        $classes = 'block w-full ps-3 pe-4 py-2 border-s-4 border-transparent text-start text-base font-medium text-neutral-600 transition-colors';
    }
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
