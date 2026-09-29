<?php

namespace App\Http\Controllers;

use App\Models\BreedingRecord;
use App\Models\Pig;
use App\Models\PregnancyRecord;
use App\Services\ReproductionService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class ReproductionController extends Controller
{
    public function __construct(private readonly ReproductionService $service) {}

    public function index()
    {
        $scope = BranchScope::ids(auth()->user());

        $breedings = BreedingRecord::with(['sow', 'boar'])
            ->when($scope !== null, fn ($q) => $q->whereHas('sow', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderByDesc('bred_at')->paginate(20);

        $pregnancies = PregnancyRecord::with('sow')
            ->when($scope !== null, fn ($q) => $q->whereHas('sow', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderByDesc('checked_at')->paginate(20);

        return view('reproduction.index', compact('breedings', 'pregnancies'));
    }

    public function createMating()
    {
        $scope = BranchScope::ids(auth()->user());

        $sows = Pig::where('sex', 'betina')
            ->whereNotIn('status', ['mati', 'dijual'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();

        $boars = Pig::where('sex', 'jantan')
            ->whereNotIn('status', ['mati', 'dijual'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->orderBy('code')->get();

        return view('reproduction.mating', compact('sows', 'boars'));
    }

    public function storeMating(Request $request)
    {
        $data = $request->validate([
            'sow_id' => 'required|exists:pigs,id',
            'boar_id' => 'nullable|exists:pigs,id',
            'bred_at' => 'required|date|before_or_equal:today',
            'method' => 'required|in:alami,inseminasi',
            'technician' => 'nullable|string|max:100',
            'dose' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $sow = Pig::findOrFail($data['sow_id']);
        $boar = isset($data['boar_id']) ? Pig::find($data['boar_id']) : null;

        $this->service->mate($sow, $boar, $data);

        return redirect()->route('reproduction.index')->with('status', 'Perkawinan tercatat.');
    }

    public function createPregnancy(BreedingRecord $breeding)
    {
        return view('reproduction.pregnancy', compact('breeding'));
    }

    public function storePregnancy(Request $request, BreedingRecord $breeding)
    {
        $data = $request->validate([
            'checked_at' => 'required|date|before_or_equal:today',
            'result' => 'required|in:positif,negatif',
            'method' => 'required|in:ultrasound,manual',
        ]);

        $this->service->checkPregnancy($breeding, $data);

        return redirect()->route('reproduction.index')->with('status', 'Pemeriksaan kebuntingan tersimpan.');
    }
}
