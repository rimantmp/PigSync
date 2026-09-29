<?php

namespace App\Services;

use App\Models\Death;
use App\Models\HealthRecord;
use App\Models\Pen;
use App\Models\PigMovement;
use App\Models\PigWeight;
use App\Support\ReportFilters;
use Illuminate\Database\Eloquent\Collection;

/**
 * Sumber data tunggal untuk 5 laporan.
 *
 * Dipakai oleh ReportController (halaman) dan ReportExportController (PDF/CSV)
 * supaya data yang tampil dan file yang diunduh tidak pernah berbeda.
 */
class ReportService
{
    public function __construct(
        private readonly PopulationService $population,
    ) {}

    public function population(ReportFilters $filters): Collection
    {
        $pens = Pen::with('branch')
            ->when($filters->branchIds !== null, fn ($q) => $q->whereIn('branch_id', $filters->branchIds))
            ->when($filters->branchId !== null, fn ($q) => $q->where('branch_id', $filters->branchId))
            ->get();

        $counts = $this->population->bulkPenPopulation($pens);

        foreach ($pens as $pen) {
            $pen->population = $counts[$pen->id] ?? 0;
        }

        return $pens;
    }

    public function growth(ReportFilters $filters): Collection
    {
        $scope = $filters->branchIds;

        return PigWeight::with('pig.pen.branch')
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->when($filters->from, fn ($q, $v) => $q->whereDate('weighed_at', '>=', $v))
            ->when($filters->to, fn ($q, $v) => $q->whereDate('weighed_at', '<=', $v))
            ->orderBy('weighed_at')
            ->get();
    }

    public function deaths(ReportFilters $filters): Collection
    {
        $scope = $filters->branchIds;

        return Death::with('pig.pen.branch')
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)))
            ->when($filters->from, fn ($q, $v) => $q->whereDate('died_at', '>=', $v))
            ->when($filters->to, fn ($q, $v) => $q->whereDate('died_at', '<=', $v))
            ->orderByDesc('died_at')
            ->get();
    }

    public function movements(ReportFilters $filters): Collection
    {
        $scope = $filters->branchIds;

        return PigMovement::with(['pig.pen.branch', 'fromPen', 'toPen'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->when($filters->from, fn ($q, $v) => $q->whereDate('moved_at', '>=', $v))
            ->when($filters->to, fn ($q, $v) => $q->whereDate('moved_at', '<=', $v))
            ->orderByDesc('moved_at')
            ->get();
    }

    public function health(ReportFilters $filters): Collection
    {
        $scope = $filters->branchIds;

        return HealthRecord::with(['pig.pen.branch', 'disease', 'medicine'])
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))))
            ->when($filters->from, fn ($q, $v) => $q->whereDate('checked_at', '>=', $v))
            ->when($filters->to, fn ($q, $v) => $q->whereDate('checked_at', '<=', $v))
            ->orderByDesc('checked_at')
            ->get();
    }
}
