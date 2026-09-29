<?php

namespace App\Services;

use App\Models\Death;
use App\Models\Pig;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DeathService
{
    public function __construct(
        private readonly PigService $pigs,
        private readonly PopulationService $population,
        private readonly AuditService $audit,
    ) {}

    /**
     * Catat kematian — BR-02: mati tidak bisa mati lagi.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(Pig $pig, array $data): Death
    {
        throw_if($pig->status === 'mati', \DomainException::class, 'Ternak sudah mati (tidak bisa mati 2x).');
        throw_if($pig->status === 'dijual', \DomainException::class, 'Ternak sudah terjual.');

        return DB::transaction(function () use ($pig, $data) {
            $beratTerakhir = $pig->weights()->latest('weighed_at')->first()?->weight;
            $estimasi = $data['estimated_loss']
                ?? ($beratTerakhir ? (float) $beratTerakhir * (float) Setting::get('harga_pasar_kg', 40000) : null);

            $death = Death::create([
                'pig_id' => $pig->id,
                'died_at' => $data['died_at'] ?? now()->toDateString(),
                'pen_id' => $pig->pen_id,
                'cause' => $data['cause'],
                'suspected_disease_id' => $data['suspected_disease_id'] ?? null,
                'disposal' => $data['disposal'] ?? null,
                'estimated_loss' => $estimasi,
                'verified_by' => $data['verified_by'] ?? null,
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $before = $pig->toArray();
            $pig->update(['status' => 'mati', 'died_at' => now()]);

            $this->pigs->history($pig, 'kematian', $before['status'], 'mati', $death->cause);

            $this->audit->record('create', 'kematian', $death, ['status' => $before['status']], ['status' => 'mati'], sensitive: true, reason: 'Kematian');

            $this->population->invalidate($pig->pen);

            return $death;
        });
    }
}
