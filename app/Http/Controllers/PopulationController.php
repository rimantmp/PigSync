<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Services\PopulationService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class PopulationController extends Controller
{
    public function __construct(private readonly PopulationService $population) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = BranchScope::ids($user);

        $pens = Pen::with('branch')
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('code', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%")))
            ->get();

        foreach ($pens as $pen) {
            $pen->population = $this->population->penPopulation($pen);
            $pen->sick = $pen->pigs()->where('status', 'sakit')->count();
            $pen->fill_pct = $pen->capacity > 0 ? round($pen->population / $pen->capacity * 100) : 0;
        }

        $branches = BranchScope::branches($user);

        return view('population.index', compact('pens', 'branches'));
    }
}
