<?php

namespace App\Http\Controllers;

use App\Models\Disease;

class DiseaseController extends MasterController
{
    protected string $model = Disease::class;

    protected string $label = 'Penyakit';

    protected string $prefix = 'diseases';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'category' => ['label' => 'Kategori', 'type' => 'text'],
        'is_zoonosis' => ['label' => 'Zoonosis', 'type' => 'select', 'options' => ['0' => 'Tidak', '1' => 'Ya']],
        'protocol' => ['label' => 'Protokol', 'type' => 'textarea'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:diseases,code',
        'name' => 'required|string|max:255',
        'category' => 'nullable|string|max:100',
        'is_zoonosis' => 'required|in:0,1',
        'protocol' => 'nullable|string',
    ];
}
