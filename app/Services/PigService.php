<?php

namespace App\Services;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigHistory;
use App\Models\PigPhase;
use App\Support\BranchScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PigService
{
    public function __construct(
        private readonly PopulationService $population,
        private readonly AuditService $audit,
    ) {}

    /**
     * Tentukan fase otomatis dari usia (hari).
     */
    public function phaseForAge(int $ageDays): ?PigPhase
    {
        return PigPhase::where(fn ($q) => $q->whereNull('age_min')->orWhere('age_min', '<=', $ageDays))
            ->where(fn ($q) => $q->whereNull('age_max')->orWhere('age_max', '>=', $ageDays))
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Registrasi babi baru + penempatan awal wajib (BR-08).
     *
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): Pig
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $pen = Pen::findOrFail($data['pen_id']);

            // BR-08 + BR-07: kandang dalam scope, tidak penuh, ternak wajib ditempatkan
            $this->assertPenAvailable($pen, $user);

            $birthDate = Carbon::parse($data['birth_date']);
            $ageDays = (int) $birthDate->diffInDays(now());
            $phase = isset($data['phase_id'])
                ? PigPhase::find($data['phase_id'])
                : $this->phaseForAge(max(0, $ageDays));

            $code = CodeGenerator::pigUnique($pen->branch, phase: $phase);

            $pig = Pig::create([
                'code' => $code,
                'tag_id' => $data['tag_id'] ?? null,
                'rfid' => $data['rfid'] ?? null,
                'sex' => $data['sex'],
                'breed_id' => $data['breed_id'] ?? null,
                'birth_date' => $birthDate,
                'origin_type' => $data['origin_type'] ?? 'internal',
                'origin_ref' => $data['origin_ref'] ?? null,
                'sire_id' => $data['sire_id'] ?? null,
                'dam_id' => $data['dam_id'] ?? null,
                'pen_id' => $pen->id,
                'phase_id' => $phase?->id,
                'status' => 'aktif',
                'initial_weight' => $data['initial_weight'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->history($pig, 'registrasi', null, 'aktif', 'Registrasi + penempatan awal di '.$pen->name);

            $this->audit->record('create', 'ternak', $pig, null, $pig->toArray());

            $this->population->invalidate($pen);

            return $pig;
        });
    }

    /**
     * Update data dasar ternak (bukan transaksi).
     */
    public function update(Pig $pig, array $data): Pig
    {
        return DB::transaction(function () use ($pig, $data) {
            $before = $pig->toArray();

            $pig->fill($data);
            $pig->save();

            $this->audit->record('update', 'ternak', $pig, $before, $pig->toArray());

            return $pig;
        });
    }

    public function history(Pig $pig, string $event, ?string $from, ?string $to, ?string $notes = null): PigHistory
    {
        return PigHistory::create([
            'pig_id' => $pig->id,
            'event_type' => $event,
            'from_value' => $from,
            'to_value' => $to,
            'event_date' => now()->toDateString(),
            'user_id' => Auth::id(),
            'notes' => $notes,
        ]);
    }

    /**
     * Assert kandang tersedia untuk ditempati (BR-07).
     */
    public function assertPenAvailable(Pen $pen, $user, ?int $ignorePigId = null): void
    {
        $scope = BranchScope::ids($user);
        if ($scope !== null && ! in_array($pen->branch_id, $scope, true)) {
            throw new HttpException(403, 'Kandang di luar scope akses Anda.');
        }

        $populasi = $this->population->penPopulation($pen);
        if ($pen->capacity > 0 && $populasi >= $pen->capacity) {
            throw new \DomainException("Kandang {$pen->name} penuh (kapasitas {$pen->capacity}).");
        }
    }
}
