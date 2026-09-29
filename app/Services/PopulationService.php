<?php

namespace App\Services;

use App\Models\Pen;
use App\Models\Pig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PopulationService
{
    /**
     * Populasi kandang = hasil kalkulasi, bukan kolom manual (BR-05).
     *
     * `pigs.pen_id` adalah lokasi ternak **saat ini** (diperbarui saat pindah).
     * Maka populasi kini = jumlah babi hidup yang lokasinya kandang ini.
     * Kematian/penjualan mengubah status ternak, sehingga otomatis keluar dari
     * hitungan. `pig_movements` tetap tersimpan utuh untuk histori & audit.
     */
    public function penPopulation(Pen $pen, ?Carbon $at = null): int
    {
        $at ??= now();

        return Cache::remember(
            "pen.{$pen->id}.{$at->toDateString()}",
            now()->addMinutes(10),
            function () use ($pen, $at) {
                return $this->computePenPopulation($pen, $at);
            }
        );
    }

    /**
     * Populasi seluruh kandang dalam cabang.
     *
     * @return array<string, int> key = pen_id
     */
    public function branchPopulation(string $branchId, ?Carbon $at = null): array
    {
        $pens = Pen::where('branch_id', $branchId)->get();

        $result = [];
        foreach ($pens as $pen) {
            $result[$pen->id] = $this->penPopulation($pen, $at);
        }

        return $result;
    }

    public function computePenPopulation(Pen $pen, Carbon $at): int
    {
        return (int) Pig::withTrashed()
            ->where('pen_id', $pen->id)
            ->whereNotIn('status', ['mati', 'dijual', 'afkir'])
            ->count();
    }

    /**
     * Invalidate cache populasi terkait kandang.
     */
    public function invalidate(Pen $pen): void
    {
        Cache::forget("pen.{$pen->id}.".now()->toDateString());
    }

    /**
     * Jumlah babi hidup per fase karyawan kandang (untuk dashboard).
     */
    public function aliveByPhase(?int $branchId = null): array
    {
        $query = Pig::query()->whereNotIn('status', ['mati', 'dijual', 'afkir']);

        if ($branchId) {
            $query->whereIn('pen_id', Pen::where('branch_id', $branchId)->pluck('id'));
        }

        return $query->join('pig_phases', 'pigs.phase_id', '=', 'pig_phases.id')
            ->selectRaw('pig_phases.name as fase, count(*) as jumlah')
            ->groupBy('pig_phases.name')
            ->pluck('jumlah', 'fase')
            ->all();
    }

    /**
     * Rekonsiliasi: selisih populasi sistem vs fisik kandang.
     */
    public function reconcile(Pen $pen, int $fisik): int
    {
        return $fisik - $this->penPopulation($pen);
    }
}
