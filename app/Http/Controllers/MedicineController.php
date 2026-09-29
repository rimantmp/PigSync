<?php

namespace App\Http\Controllers;

use App\Models\Medicine;

class MedicineController extends MasterController
{
    protected string $model = Medicine::class;

    protected string $label = 'Obat & Vaksin';

    protected string $prefix = 'medicines';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'kind' => ['label' => 'Jenis', 'type' => 'select', 'options' => ['medicine', 'vaccine']],
        'withdrawal_days' => ['label' => 'Masa Larangan (hari)', 'type' => 'number'],
        'auto_deduct' => ['label' => 'Auto-deduct stok', 'type' => 'select', 'options' => ['1' => 'Ya', '0' => 'Tidak']],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:medicines,code',
        'name' => 'required|string|max:255',
        'kind' => 'required|in:medicine,vaccine',
        'withdrawal_days' => 'nullable|integer|min:0',
        'auto_deduct' => 'required|in:0,1',
    ];
}
