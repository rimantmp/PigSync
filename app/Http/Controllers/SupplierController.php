<?php

namespace App\Http\Controllers;

use App\Models\Supplier;

class SupplierController extends MasterController
{
    protected string $model = Supplier::class;

    protected string $label = 'Supplier';

    protected string $prefix = 'suppliers';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'type' => ['label' => 'Tipe', 'type' => 'select', 'options' => ['pakan', 'obat', 'ternak', 'lain']],
        'contact' => ['label' => 'Kontak', 'type' => 'text'],
        'phone' => ['label' => 'Telepon', 'type' => 'text'],
        'bank' => ['label' => 'Bank', 'type' => 'text'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:suppliers,code',
        'name' => 'required|string|max:255',
        'type' => 'required|in:pakan,obat,ternak,lain',
        'contact' => 'nullable|string',
        'phone' => 'nullable|string|max:30',
        'bank' => 'nullable|string',
    ];
}
