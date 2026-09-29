<?php

namespace App\Http\Controllers;

use App\Models\Birth;
use App\Models\Death;
use App\Models\Pig;
use App\Services\PopulationService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly PopulationService $population) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = BranchScope::ids($user);

        $penFilter = $scope !== null
            ? fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope))
            : fn ($q) => $q;

        $alive = Pig::whereNotIn('status', ['mati', 'dijual', 'afkir']);

        if ($scope !== null) {
            $alive->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope));
        }

        $totalPopulasi = $alive->count();
        $sakit = Pig::whereIn('status', ['sakit', 'karantina'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->count();
        $mati = Death::whereMonth('died_at', now()->month)
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->count();
        $lahir = Birth::whereMonth('farrowed_at', now()->month)
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->sum('born_alive');

        $byFase = $this->population->aliveByPhase($scope !== null ? null : null);

        return view('dashboard', compact('totalPopulasi', 'sakit', 'mati', 'lahir'));
    }
}
