<?php

namespace App\Http\Controllers;

use App\Models\Unit;

class UnitController extends MasterController
{
    protected string $model = Unit::class;

    protected string $label = 'Satuan';

    protected string $prefix = 'units';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'conversion' => ['label' => 'Konversi', 'type' => 'number'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:units,code',
        'name' => 'required|string|max:255',
        'conversion' => 'nullable|numeric',
    ];
}
