<?php

namespace App\Http\Controllers;

use App\Models\PigPhase;

class PigPhaseController extends MasterController
{
    protected string $model = PigPhase::class;

    protected string $label = 'Fase Babi';

    protected string $prefix = 'phases';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'sort_order' => ['label' => 'Urutan', 'type' => 'number'],
        'age_min' => ['label' => 'Usia Min (hari)', 'type' => 'number'],
        'age_max' => ['label' => 'Usia Max (hari)', 'type' => 'number'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:pig_phases,code',
        'name' => 'required|string|max:255',
        'sort_order' => 'nullable|integer',
        'age_min' => 'nullable|integer',
        'age_max' => 'nullable|integer',
    ];
}
