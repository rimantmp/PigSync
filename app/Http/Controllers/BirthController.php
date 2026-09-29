<?php

namespace App\Http\Controllers;

use App\Models\Birth;
use App\Models\Pen;
use App\Models\Pig;
use App\Services\BirthService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class BirthController extends Controller
{
    public function __construct(private readonly BirthService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $births = Birth::with(['sow', 'pen.branch'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderByDesc('farrowed_at')
            ->paginate(20)
            ->withQueryString();

        return view('births.index', compact('births'));
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());

        $sows = Pig::where('sex', 'betina')
            ->whereIn('status', ['bunting', 'menyusui', 'aktif'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();

        $pens = Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->orderBy('name')->get();

        return view('births.create', compact('sows', 'pens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sow_id' => 'nullable|exists:pigs,id',
            'farrowed_at' => 'required|date|before_or_equal:today',
            'total_born' => 'nullable|integer|min:1|max:20',
            'born_alive' => 'required|integer|min:0',
            'born_dead' => 'nullable|integer|min:0',
            'mummified' => 'nullable|integer|min:0',
            'avg_weight' => 'nullable|numeric|gt:0',
            'pen_id' => 'required|exists:pens,id',
            'assistant' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $sow = isset($data['sow_id']) ? Pig::find($data['sow_id']) : null;

        $birth = $this->service->farrow($sow, $data);

        return redirect()->route('births.index')->with('status', "Kelahiran tercatat. {$birth->born_alive} piglet terdaftar.");
    }
}
