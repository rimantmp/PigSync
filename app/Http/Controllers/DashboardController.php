<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\BranchScope;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function index(Request $request)
    {
        $scope = BranchScope::ids(auth()->user());

        return view('dashboard', [
            'summary' => $this->dashboard->summary($scope),
            'byPhase' => $this->dashboard->byPhase($scope),
            'byPen' => $this->dashboard->byPen($scope),
            'weightTrend' => $this->dashboard->weightTrend($scope),
            'sales' => $this->dashboard->salesSummary($scope),
            'finance' => $this->dashboard->financeSummary($scope),
            'activity' => $this->dashboard->recentActivity($scope),
        ]);
    }
}
