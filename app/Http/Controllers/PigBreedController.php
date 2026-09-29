<?php

namespace App\Http\Controllers;

use App\Models\PigBreed;

class PigBreedController extends MasterController
{
    protected string $model = PigBreed::class;

    protected string $label = 'Jenis Babi';

    protected string $prefix = 'breeds';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'description' => ['label' => 'Deskripsi', 'type' => 'textarea'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:pig_breeds,code',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];
}
