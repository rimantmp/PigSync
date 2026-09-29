<?php

namespace App\Http\Controllers;

use App\Models\Death;
use App\Models\HealthRecord;
use App\Models\Pen;
use App\Models\PigMovement;
use App\Models\PigWeight;
use App\Services\PopulationService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly PopulationService $population) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        return view('reports.index', [
            'branches' => BranchScope::branches(auth()->user()),
            'pens' => Pen::when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))->get(),
        ]);
    }

    public function population(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $pens = Pen::with('branch')
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->get();

        foreach ($pens as $pen) {
            $pen->population = $this->population->penPopulation($pen);
        }

        return view('reports.population', compact('pens'));
    }

    public function growth(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $weights = PigWeight::with('pig.pen.branch')
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderBy('weighed_at')
            ->get();

        return view('reports.growth', compact('weights'));
    }

    public function deaths(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $deaths = Death::with(['pig', 'pen.branch'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->when($request->from, fn ($q, $v) => $q->whereDate('died_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('died_at', '<=', $v))
            ->orderByDesc('died_at')->get();

        return view('reports.deaths', compact('deaths'));
    }

    public function movements(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $movements = PigMovement::with(['pig', 'fromPen', 'toPen'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->when($request->from, fn ($q, $v) => $q->whereDate('moved_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('moved_at', '<=', $v))
            ->orderByDesc('moved_at')->get();

        return view('reports.movements', compact('movements'));
    }

    public function health(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        $records = HealthRecord::with(['pig', 'disease', 'medicine'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->orderByDesc('checked_at')->get();

        return view('reports.health', compact('records'));
    }
}
