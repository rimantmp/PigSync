<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends MasterController
{
    protected string $model = Customer::class;

    protected string $label = 'Pelanggan';

    protected string $prefix = 'customers';

    protected array $fields = [
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'type' => ['label' => 'Tipe', 'type' => 'select', 'options' => ['pejantan', 'afkir', 'peternakan', 'konsumsi']],
        'contact' => ['label' => 'Kontak', 'type' => 'text'],
        'phone' => ['label' => 'Telepon', 'type' => 'text'],
        'bank' => ['label' => 'Bank', 'type' => 'text'],
    ];

    protected array $rules = [
        'code' => 'required|string|max:20|unique:customers,code',
        'name' => 'required|string|max:255',
        'type' => 'required|in:pejantan,afkir,peternakan,konsumsi',
        'contact' => 'nullable|string',
        'phone' => 'nullable|string|max:30',
        'bank' => 'nullable|string',
    ];
}
