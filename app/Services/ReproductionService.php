<?php

namespace App\Services;

use App\Models\BreedingRecord;
use App\Models\Pig;
use App\Models\PregnancyRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReproductionService
{
    public function __construct(
        private readonly PigService $pigs,
        private readonly AuditService $audit,
    ) {}

    /**
     * Perkawinan — betina aktif & belum bunting; pejantan jantan (BR-16).
     */
    public function mate(Pig $sow, ?Pig $boar, array $data): BreedingRecord
    {
        throw_if($sow->sex !== 'betina', \DomainException::class, 'Induk harus betina.');
        throw_if($sow->status === 'bunting', \DomainException::class, 'Induk sedang bunting.');
        throw_if($boar && $boar->sex !== 'jantan', \DomainException::class, 'Pejantan harus jantan.');

        return DB::transaction(function () use ($sow, $boar, $data) {
            $record = BreedingRecord::create([
                'sow_id' => $sow->id,
                'boar_id' => $boar?->id,
                'bred_at' => $data['bred_at'] ?? now()->toDateString(),
                'method' => $data['method'] ?? 'alami',
                'technician' => $data['technician'] ?? null,
                'dose' => $data['dose'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $this->audit->record('create', 'reproduksi', $record, null, $record->toArray());

            return $record;
        });
    }

    /**
     * Pemeriksaan kebuntingan — positif -> status bunting + est. kelahiran.
     */
    public function checkPregnancy(BreedingRecord $breeding, array $data): PregnancyRecord
    {
        $farrowingDays = (int) Setting::get('farrowing_days', 114); // Q2

        return DB::transaction(function () use ($breeding, $data, $farrowingDays) {
            $positive = ($data['result'] ?? 'positif') === 'positif';

            $record = PregnancyRecord::create([
                'breeding_id' => $breeding->id,
                'sow_id' => $breeding->sow_id,
                'checked_at' => $data['checked_at'] ?? now()->toDateString(),
                'result' => $positive ? 'positif' : 'negatif',
                'method' => $data['method'] ?? 'ultrasound',
                'expected_farrow_at' => $positive
                    ? Carbon::parse($breeding->bred_at)->addDays($farrowingDays)->toDateString()
                    : null,
                'status' => $positive ? 'bunting' : 'tidak_bunting',
            ]);

            $sow = $breeding->sow;
            if ($positive) {
                $sow->update(['status' => 'bunting']);
                $this->pigs->history($sow, 'status', 'aktif', 'bunting', 'Hasil pemeriksaan kebuntingan positif');
            } else {
                $sow->update(['status' => 'aktif']);
                $this->pigs->history($sow, 'status', 'bunting', 'aktif', 'Hasil pemeriksaan kebuntingan negatif');
            }

            $this->audit->record('create', 'reproduksi', $record, null, $record->toArray());

            return $record;
        });
    }
}
