<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\PigWeight;
use App\Services\WeighingService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class WeighingController extends Controller
{
    public function __construct(private readonly WeighingService $service) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $weights = PigWeight::with('pig.pen.branch')
            ->whereHas('pig', fn ($q) => $scope !== null ? $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)) : $q)
            ->when($request->search, fn ($q, $s) => $q->whereHas('pig', fn ($p) => $p->where('code', 'like', "%{$s}%")))
            ->orderByDesc('weighed_at')
            ->paginate(20)
            ->withQueryString();

        return view('weighings.index', compact('weights'));
    }

    public function create()
    {
        $scope = BranchScope::ids(auth()->user());
        $pigs = Pig::whereNotIn('status', ['mati'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')
            ->get();

        return view('weighings.create', compact('pigs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pig_id' => 'required|exists:pigs,id',
            'weighed_at' => 'required|date|before_or_equal:today',
            'weight' => 'required|numeric|gt:0',
            'method' => 'required|in:individu,kelompok',
            'notes' => 'nullable|string',
        ]);

        $pig = Pig::findOrFail($data['pig_id']);
        $result = $this->service->record($pig, $data);

        return redirect()->route('weighings.index')->with('status', sprintf(
            'Penimbangan tersimpan. ADG: %s',
            $result['adg'] !== null ? $result['adg'].' kg/hari' : 'n/a'
        ));
    }
}
