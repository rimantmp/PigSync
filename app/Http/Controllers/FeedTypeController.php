<?php

namespace App\Http\Controllers;

use App\Models\FeedType;

class FeedTypeController extends MasterController
{
    protected string $model = FeedType::class;

    protected string $label = 'Jenis Pakan';

    protected string $prefix = 'feed-types';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['starter', 'grower', 'finisher', 'sow']],
        'unit_id' => ['label' => 'Satuan', 'type' => 'select', 'options' => 'units'],
        'default_price' => ['label' => 'Harga Default', 'type' => 'number'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:feed_types,code',
        'name' => 'required|string|max:255',
        'category' => 'required|in:starter,grower,finisher,sow',
        'unit_id' => 'nullable|exists:units,id',
        'default_price' => 'nullable|numeric',
    ];
}
