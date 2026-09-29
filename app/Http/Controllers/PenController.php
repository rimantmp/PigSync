<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Services\PopulationService;

class PenController extends MasterController
{
    protected string $model = Pen::class;

    protected string $label = 'Kandang';

    protected string $prefix = 'pens';

    public function __construct(private readonly PopulationService $population) {}

    protected array $fields = [
        'branch_id' => ['label' => 'Cabang', 'type' => 'select', 'required' => true, 'options' => 'branches'],
        'area_id' => ['label' => 'Area', 'type' => 'select', 'options' => 'areas'],
        'code' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
        'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
        'type' => ['label' => 'Tipe', 'type' => 'select', 'options' => ['fattening', 'grower', 'finisher', 'farrowing', 'gestation', 'weaning', 'quarantine']],
        'capacity' => ['label' => 'Kapasitas', 'type' => 'number', 'required' => true],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif', 'nonaktif']],
    ];

    protected array $rules = [
        'branch_id' => 'required|exists:branches,id',
        'area_id' => 'nullable|exists:areas,id',
        'code' => 'required|string|max:20|unique:pens,code',
        'name' => 'required|string|max:255',
        'type' => 'required|string',
        'capacity' => 'required|integer|min:1',
        'status' => 'required|in:aktif,nonaktif',
    ];

    protected function fieldColumns(): array
    {
        return ['code', 'name', 'branch_id', 'type', 'capacity'];
    }

    public function index()
    {
        $rows = $this->query()->with('branch')->orderBy('id')->paginate(20);

        foreach ($rows as $pen) {
            $pen->current_population = $this->population->penPopulation($pen);
        }

        return view('master.index', [
            'rows' => $rows,
            'label' => $this->label,
            'definitions' => $this->fields,
            'columns' => $this->fieldColumns(),
            'prefix' => $this->prefix,
        ]);
    }
}
