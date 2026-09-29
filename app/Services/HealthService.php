<?php

namespace App\Services;

use App\Models\HealthRecord;
use App\Models\Medicine;
use App\Models\Pig;
use Illuminate\Support\Facades\DB;

class HealthService
{
    public function __construct(
        private readonly PigService $pigs,
        private readonly AuditService $audit,
    ) {}

    /**
     * Pemeriksaan/pengobatan. Auto-deduct stok bila item tandai auto_deduct (BR-15/Q4).
     *
     * @param  array<string, mixed>  $data
     */
    public function record(Pig $pig, array $data): HealthRecord
    {
        throw_if($pig->status === 'mati', \DomainException::class, 'Ternak sudah mati.');

        return DB::transaction(function () use ($pig, $data) {
            $before = ['status' => $pig->status];

            $medicine = isset($data['medicine_id']) ? Medicine::find($data['medicine_id']) : null;

            $withdrawalUntil = null;
            if ($medicine && ($medicine->withdrawal_days ?? 0) > 0) {
                $withdrawalUntil = now()->addDays($medicine->withdrawal_days)->toDateString();
            }

            $record = HealthRecord::create([
                'pig_id' => $pig->id,
                'checked_at' => $data['checked_at'] ?? now()->toDateString(),
                'symptoms' => $data['symptoms'] ?? null,
                'disease_id' => $data['disease_id'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'medicine_id' => $medicine?->id,
                'dose' => $data['dose'] ?? null,
                'route' => $data['route'] ?? null,
                'withdrawal_until' => $withdrawalUntil,
                'vet' => $data['vet'] ?? null,
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Status ternak -> sakit/karantina sesuai hasil
            $newStatus = $data['status'] ?? 'sakit';
            if (in_array($newStatus, ['sakit', 'karantina'], true) && $pig->status !== $newStatus) {
                $pig->update(['status' => $newStatus]);
                $this->pigs->history($pig, 'status', $before['status'], $newStatus, 'Pemeriksaan kesehatan');
            }

            $this->audit->record('create', 'kesehatan', $record, null, $record->toArray());

            return $record;
        });
    }

    /**
     * Sembuh / selesai karantina -> status aktif kembali.
     */
    public function recover(Pig $pig): Pig
    {
        return DB::transaction(function () use ($pig) {
            $before = $pig->status;

            $pig->update(['status' => 'aktif']);
            $this->pigs->history($pig, 'status', $before, 'aktif', 'Sembuh / selesai karantina');

            $this->audit->record('update', 'kesehatan', $pig, ['status' => $before], ['status' => 'aktif']);

            return $pig;
        });
    }
}
