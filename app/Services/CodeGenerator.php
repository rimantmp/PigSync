<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Pig;
use App\Models\PigBreed;
use App\Models\PigPhase;
use Illuminate\Support\Facades\DB;

class CodeGenerator
{
    /**
     * Generate kode ternak unik global (BR-01):
     * `<KODE-CABANG>-<KODE-FASE/JENIS>-<TAHUN>-<SEQ4DIGIT>`.
     */
    public static function pig(Branch $branch, ?PigBreed $breed = null, ?PigPhase $phase = null, ?string $year = null): string
    {
        $year ??= now()->format('Y');

        $fase = $phase?->code;
        if (blank($fase)) {
            $fase = $breed?->code ?? 'PIG';
        }

        $prefix = strtoupper($branch->code).'-'.strtoupper($fase).'-'.$year;

        $max = Pig::where('code', 'like', $prefix.'-%')
            ->orderByDesc('code')
            ->value('code');

        $seq = 1;
        if ($max && preg_match('/-(\d+)$/', $max, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate urutan dalam transaksi DB + cek unik untuk hindari race.
     */
    public static function pigUnique(Branch $branch, ?PigBreed $breed = null, ?PigPhase $phase = null, ?string $year = null): string
    {
        return DB::transaction(function () use ($branch, $breed, $phase, $year) {
            $code = static::pig($branch, $breed, $phase, $year);

            while (Pig::withTrashed()->where('code', $code)->exists()) {
                $code = static::pig($branch, $breed, $phase, $year);
            }

            return $code;
        });
    }
}
