<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Birth;
use App\Models\Death;
use App\Models\Expense;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigWeight;
use App\Models\Revenue;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Agregasi data untuk dashboard.
 *
 * Semua query sudah di-scope cabang di sini, bukan di controller, supaya
 * angka yang ditampilkan tidak mungkin lolos dari filter akses.
 */
class DashboardService
{
    public function __construct(
        private readonly PopulationService $population,
    ) {}

    /**
     * @param  array<int>|null  $scope  null = semua cabang
     * @return array<string, mixed>
     */
    public function summary(?array $scope): array
    {
        $alive = $this->scopedPigs($scope)->whereNotIn('status', ['mati', 'dijual', 'afkir']);

        $totalPopulasi = (clone $alive)->count();
        $sakit = $this->scopedPigs($scope)->whereIn('status', ['sakit', 'karantina'])->count();

        $mortalityBase = $this->scopedPigs($scope)->count();

        $matiBulanIni = $this->scopedDeaths($scope)
            ->whereBetween('died_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $lahirBulanIni = $this->scopedBirths($scope)
            ->whereBetween('farrowed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('born_alive');

        return [
            'totalPopulasi' => $totalPopulasi,
            'sakit' => $sakit,
            'matiBulanIni' => $matiBulanIni,
            'lahirBulanIni' => $lahirBulanIni,
            // Basis mortality: semua ekor pernah terdaftar, bukan hanya yang hidup.
            'mortalityRate' => $mortalityBase > 0
                ? round($this->scopedDeaths($scope)->count() / $mortalityBase * 100, 1)
                : 0.0,
            'kapasitas' => $this->scopedPens($scope)->sum('capacity'),
            'fillRate' => $this->fillRate($scope),
            'alerts' => $this->alerts($scope),
        ];
    }

    /**
     * Distribusi Ternak per fase — untuk chart donut.
     *
     * @param  array<int>|null  $scope
     * @return array<int, array{name:string, jumlah:int}>
     */
    public function byPhase(?array $scope): array
    {
        $rows = $this->scopedPigs($scope)
            ->whereNotIn('status', ['mati', 'dijual', 'afkir'])
            ->join('pig_phases', 'pigs.phase_id', '=', 'pig_phases.id')
            ->selectRaw('pig_phases.name as name, count(*) as jumlah')
            ->groupBy('pig_phases.name')
            ->orderByDesc('jumlah')
            ->get();

        return $rows->map(fn ($r) => ['name' => $r->name, 'jumlah' => (int) $r->jumlah])->all();
    }

    /**
     * Populasi per kandang — untuk bar chart.
     *
     * @param  array<int>|null  $scope
     * @return array<int, array{name:string, populasi:int, kapasitas:int, fill:int}>
     */
    public function byPen(?array $scope): array
    {
        $pens = $this->scopedPens($scope)->with('branch')->get();
        $counts = $this->population->bulkPenPopulation($pens);

        return $pens->map(function (Pen $pen) use ($counts) {
            $pop = $counts[$pen->id] ?? 0;
            $cap = (int) $pen->capacity;

            return [
                'name' => $pen->name,
                'cabang' => $pen->branch?->name ?? '-',
                'populasi' => $pop,
                'kapasitas' => $cap,
                'fill' => $cap > 0 ? (int) min(100, round($pop / $cap * 100)) : 0,
            ];
        })->sortByDesc('populasi')->values()->all();
    }

    /**
     * Tren berat rata-rata 6 titik terakhir.
     *
     * @param  array<int>|null  $scope
     * @return array<int, array{tanggal:string, label:string, rerata:float, jumlah:int}>
     */
    public function weightTrend(?array $scope): array
    {
        return $this->scopedWeights($scope)
            ->get(['weighed_at', 'weight'])
            ->groupBy(fn ($w) => $w->weighed_at->format('Y-m-d'))
            ->map(fn (Collection $group, $date) => [
                'tanggal' => $date,
                'label' => Carbon::parse($date)->format('d M'),
                'rerata' => round((float) $group->avg('weight'), 1),
                'jumlah' => $group->count(),
            ])
            ->sortBy('tanggal')
            ->take(-6)
            ->values()
            ->all();
    }

    /**
     * Penjualan lunas vs belum bayar — piutang yang perlu ditindaklanjuti.
     *
     * @param  array<int>|null  $scope
     * @return array{belum_bayar:int, belum_bayar_nominal:float, lunas:int, lunas_nominal:float}
     */
    public function salesSummary(?array $scope): array
    {
        $query = Sale::query()
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope));

        $unpaid = (clone $query)->where('payment_status', 'belum_bayar');
        $paid = (clone $query)->where('payment_status', 'lunas');

        return [
            'belum_bayar' => $unpaid->count(),
            'belum_bayar_nominal' => (float) $unpaid->sum('total'),
            'lunas' => $paid->count(),
            'lunas_nominal' => (float) $paid->sum('total'),
        ];
    }

    /**
     * Pendapatan vs biaya bulan berjalan.
     *
     * @param  array<int>|null  $scope
     * @return array{pendapatan:float, biaya:float, profit:float}
     */
    public function financeSummary(?array $scope): array
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $pendapatan = (float) Revenue::query()
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->whereBetween('received_at', [$from, $to])
            ->sum('amount');

        $biaya = (float) Expense::query()
            ->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope))
            ->whereBetween('spent_at', [$from, $to])
            ->sum('amount');

        return [
            'pendapatan' => $pendapatan,
            'biaya' => $biaya,
            'profit' => $pendapatan - $biaya,
        ];
    }

    /**
     * Aktivitas terbaru dari audit log.
     *
     * @param  array<int>|null  $scope
     * @return Collection<int, AuditLog>
     */
    public function recentActivity(?array $scope, int $limit = 8): Collection
    {
        return AuditLog::with('user')
            ->when($scope !== null, fn ($q) => $q->whereIn('module', [
                'ternak', 'gudang', 'pembelian', 'penjualan', 'kesehatan', 'reproduksi',
            ]))
            ->latest('at')
            ->limit($limit)
            ->get();
    }

    /**
     * Peringatan yang perlu tindakan dan tidak bisa diabaikan.
     *
     * @param  array<int>|null  $scope
     * @return array<int, array{type:string, tone:string, title:string, detail:string, link:?string}>
     */
    public function alerts(?array $scope): array
    {
        $alerts = [];

        // 1. Stok di bawah minimum
        $stokRendah = $this->scopedStock($scope)
            ->whereColumn('qty', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->get();

        if ($jumlahStokRendah = $stokRendah->count()) {
            $alerts[] = [
                'type' => 'stok',
                'tone' => 'warning',
                'title' => $jumlahStokRendah.' item di bawah stok minimum',
                'detail' => 'Segera buat purchase request agar pasokan tidak terhenti.',
                'link' => route('feeds.index'),
            ];
        }

        // 2. Kandang mendekati kapasitas (>= 90% sesuai setting)
        $threshold = (int) Setting::get('kapasitas_alert_pct', 90);

        $penMepuh = collect($this->byPen($scope))
            ->filter(fn ($p) => $p['fill'] >= $threshold)
            ->values();

        if ($penMepuh->isNotEmpty()) {
            $alerts[] = [
                'type' => 'kapasitas',
                'tone' => 'warning',
                'title' => $penMepuh->count().' kandang mencapai '.$threshold.'% isi',
                'detail' => 'Penempatan baru akan ditolak. Periksa penempatan ulang.',
                'link' => route('population.index'),
            ];
        }

        // 3. Mortalitas bulan ini melewati ambang
        $mortalitas = (int) Setting::get('death_alert_threshold', 2);

        $mati = $this->scopedDeaths($scope)
            ->whereBetween('died_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        if ($mati >= $mortalitas) {
            $alerts[] = [
                'type' => 'mortalitas',
                'tone' => 'error',
                'title' => 'Kematian bulan ini '.$mati.' ekor',
                'detail' => 'Ambang batas sistem '.$mortalitas.' ekor. Tinjau penyebab kematian.',
                'link' => route('reports.deaths'),
            ];
        }

        // 4. Ternak sakit
        $sakit = $this->scopedPigs($scope)->whereIn('status', ['sakit', 'karantina'])->count();

        if ($sakit > 0) {
            $alerts[] = [
                'type' => 'kesehatan',
                'tone' => 'info',
                'title' => $sakit.' ekor sakit atau karantina',
                'detail' => 'Pastikan jadwal vaksinasi dan pengobatan berjalan.',
                'link' => route('health.index'),
            ];
        }

        return $alerts;
    }

    /**
     * @param  array<int>|null  $scope
     */
    private function fillRate(?array $scope): int
    {
        $pens = $this->scopedPens($scope)->get();
        $kapasitas = $pens->sum('capacity');

        if ($kapasitas <= 0) {
            return 0;
        }

        $terisi = array_sum($this->population->bulkPenPopulation($pens));

        return (int) min(100, round($terisi / $kapasitas * 100));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<Pig>
     */
    private function scopedPigs(?array $scope)
    {
        return Pig::query()
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<Pen>
     */
    private function scopedPens(?array $scope)
    {
        return Pen::query()
            ->when($scope !== null, fn ($q) => $q->where('branch_id', $scope));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<Death>
     */
    private function scopedDeaths(?array $scope)
    {
        return Death::query()
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<Birth>
     */
    private function scopedBirths(?array $scope)
    {
        return Birth::query()
            ->when($scope !== null, fn ($q) => $q->whereHas('pen', fn ($p) => $p->whereIn('branch_id', $scope)));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<PigWeight>
     */
    private function scopedWeights(?array $scope)
    {
        return PigWeight::query()
            ->when($scope !== null, fn ($q) => $q->whereHas('pig', fn ($p) => $p->whereHas('pen', fn ($x) => $x->whereIn('branch_id', $scope))));
    }

    /**
     * @param  array<int>|null  $scope
     * @return Builder<Stock>
     */
    private function scopedStock(?array $scope)
    {
        return Stock::query()
            ->whereHas('warehouse', fn ($w) => $w->when($scope !== null, fn ($q) => $q->whereIn('branch_id', $scope)));
    }
}
