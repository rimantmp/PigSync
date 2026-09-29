<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Support\BranchScope;
use App\Support\ReportFilters;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function index(Request $request)
    {
        return view('reports.index', [
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }

    public function population(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.population', [
            'pens' => $this->reports->population($filters),
            'filters' => $filters,
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }

    public function growth(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.growth', [
            'weights' => $this->reports->growth($filters),
            'filters' => $filters,
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }

    public function deaths(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.deaths', [
            'deaths' => $this->reports->deaths($filters),
            'filters' => $filters,
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }

    public function movements(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.movements', [
            'movements' => $this->reports->movements($filters),
            'filters' => $filters,
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }

    public function health(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.health', [
            'records' => $this->reports->health($filters),
            'filters' => $filters,
            'branches' => BranchScope::branches(auth()->user()),
        ]);
    }
}
