@props(['value' => null])

<label {{ $attributes->merge(['class' => 'block text-[13px] font-medium text-neutral-700']) }}>
    {{ $value ?? $slot }}
</label>
