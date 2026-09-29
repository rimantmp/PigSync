<?php

use App\Models\Area;
use App\Models\Branch;
use App\Models\Unit;

if (! function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        return match ($status) {
            'aktif' => 'bg-emerald-100 text-emerald-700',
            'sakit', 'karantina' => 'bg-amber-100 text-amber-700',
            'bunting', 'menyusui' => 'bg-purple-100 text-purple-700',
            'mati' => 'bg-red-100 text-red-700',
            'dijual', 'afkir' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}

if (! function_exists('fieldOptions')) {
    /**
     * Resolve opsi form select. `options` bisa:
     *  - array asosiatif [value => label]
     *  - string nama entitas ("branches", "areas", "units")
     *
     * @return array<mixed, string>
     */
    function fieldOptions(array|string $options, string $fieldName): array
    {
        if (is_array($options)) {
            return $options;
        }

        return match ($options) {
            'branches' => Branch::orderBy('name')->pluck('name', 'id')->all(),
            'areas' => Area::orderBy('name')->pluck('name', 'id')->all(),
            'units' => Unit::orderBy('name')->pluck('name', 'id')->all(),
            default => [],
        };
    }
}
