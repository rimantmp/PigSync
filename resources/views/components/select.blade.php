@props(['disabled' => false])

{{-- Select konsisten dengan input (§9.4). --}}
<select @disabled($disabled) {{ $attributes->merge(['class' => 'field-control pr-9']) }}>
    {{ $slot }}
</select>
