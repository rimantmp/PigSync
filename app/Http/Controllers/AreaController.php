<?php

namespace App\Http\Controllers;

use App\Models\Area;

class AreaController extends MasterController
{
    protected string $model = Area::class;

    protected string $label = 'Area';

    protected string $prefix = 'areas';

    protected array $fields = [
        'branch_id' => ['label' => 'Cabang', 'type' => 'select', 'required' => true, 'options' => 'branches'],
        'name' => ['label' => 'Nama Area', 'type' => 'text', 'required' => true],
        'sort_order' => ['label' => 'Urutan', 'type' => 'number'],
    ];

    protected array $rules = [
        'branch_id' => 'required|exists:branches,id',
        'name' => 'required|string|max:255',
        'sort_order' => 'nullable|integer',
    ];

    protected function fieldColumns(): array
    {
        return ['branch_id', 'name', 'sort_order'];
    }
}
