<?php

namespace App\Http\Controllers;

use App\Models\Branch;

class BranchController extends MasterController
{
    protected string $model = Branch::class;

    protected string $label = 'Cabang';

    protected string $prefix = 'branches';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'address' => ['label' => 'Alamat', 'type' => 'textarea'],
        'phone' => ['label' => 'Telepon', 'type' => 'text'],
        'pic' => ['label' => 'PIC', 'type' => 'text'],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif', 'nonaktif']],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:branches,code',
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:30',
        'pic' => 'nullable|string|max:255',
        'status' => 'required|in:aktif,nonaktif',
    ];
}
