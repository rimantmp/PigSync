<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

abstract class MasterController extends Controller implements HasMiddleware
{
    /**
     * FQCN model yang dikelola.
     */
    protected string $model;

    /**
     * Nama human-readable (untuk judul).
     */
    protected string $label;

    /**
     * Prefix rute (mis. "branches").
     */
    protected string $prefix;

    /**
     * Kolom yang bisa diedit, urut tampil: name => config.
     *
     * @var array<string, array{label:string,type:string,required?:bool,options?:array|string}>
     */
    protected array $fields = [];

    /**
     * Aturan validasi simpan.
     *
     * @var array<string, string>
     */
    protected array $rules = [];

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('branch.scope'),
        ];
    }

    protected function model(): Model
    {
        return app($this->model);
    }

    protected function query()
    {
        $q = $this->model()::query();
        if (method_exists($q, 'getModel') && in_array(SoftDeletes::class, class_uses($this->model), true)) {
            // soft-delete model: default tampilkan yang aktif saja
        }

        return $q->when(request('search'), fn ($q, $s) => $q->where('code', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
    }

    public function index()
    {
        $rows = $this->query()->orderBy('id')->paginate(20);

        return view('master.index', [
            'rows' => $rows,
            'label' => $this->label,
            'definitions' => $this->fields,
            'columns' => $this->fieldColumns(),
            'prefix' => $this->prefix,
        ]);
    }

    public function create()
    {
        return view('master.form', [
            'row' => $this->model(),
            'label' => $this->label,
            'fields' => $this->fields,
            'prefix' => $this->prefix,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);

        $this->model()::create($data);

        return redirect()->route($this->prefix.'.index')->with('status', $this->label.' disimpan.');
    }

    public function edit($id)
    {
        $row = $this->model()::findOrFail($id);

        return view('master.form', [
            'row' => $row,
            'label' => $this->label,
            'fields' => $this->fields,
            'prefix' => $this->prefix,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->rulesForUpdate($id));

        $row = $this->model()::findOrFail($id);
        $row->update($data);

        return redirect()->route($this->prefix.'.index')->with('status', $this->label.' diperbarui.');
    }

    /**
     * Aturan validasi update: unik diabaikan untuk baris ini.
     *
     * @return array<string, mixed>
     */
    protected function rulesForUpdate(int $id): array
    {
        $rules = $this->rules;

        foreach ($rules as $key => $rule) {
            if (is_string($rule) && preg_match('/^unique:([^,]+),([^|]+)/', $rule, $m)) {
                $rules[$key] = preg_replace(
                    '/^unique:([^,]+),([^|]+)/',
                    'unique:'.$m[1].','.$m[2].','.$id,
                    $rule
                );
            }
        }

        return $rules;
    }

    public function destroy($id)
    {
        $row = $this->model()::findOrFail($id);

        if (in_array(SoftDeletes::class, class_uses($row), true)) {
            $row->delete();
        } else {
            $row->delete();
        }

        return back()->with('status', $this->label.' dihapus.');
    }

    /**
     * Kolom untuk tabel index.
     *
     * @return array<int, string>
     */
    protected function fieldColumns(): array
    {
        return array_slice(array_keys($this->fields), 0, 6);
    }
}
