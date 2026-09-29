<?php

namespace App\Services;

use App\Models\Birth;
use App\Models\Pen;
use App\Models\Pig;
use Illuminate\Support\Facades\DB;

class BirthService
{
    public function __construct(
        private readonly ReproductionService $reproduction,
        private readonly PigService $pigs,
        private readonly PopulationService $population,
        private readonly AuditService $audit,
    ) {}

    /**
     * Kelahiran — auto-registrasi piglet hidup (BR-14), induk -> menyusui.
     *
     * @param  array<string, mixed>  $data
     */
    public function farrow(?Pig $sow, array $data): Birth
    {
        throw_if(
            $sow && $sow->sex !== 'betina',
            \DomainException::class,
            'Induk harus betina (BR-16).'
        );

        throw_if(
            $sow && in_array($sow->status, ['mati', 'dijual'], true),
            \DomainException::class,
            'Induk sudah mati/terjual, tidak bisa melahirkan (BR-02/BR-03).'
        );

        $bornAlive = (int) $data['born_alive'];
        $bornDead = (int) ($data['born_dead'] ?? 0);
        $mummified = (int) ($data['mummified'] ?? 0);
        $total = (int) ($data['total_born'] ?? ($bornAlive + $bornDead + $mummified));

        throw_if($bornAlive + $bornDead + $mummified > $total, \DomainException::class, 'Jumlah hidup + mati + mummy melebihi total lahir.');
        throw_if($total > 20, \DomainException::class, 'Total lahir tidak realistis (>20).');

        return DB::transaction(function () use ($sow, $data, $bornAlive, $bornDead, $mummified, $total) {
            $pen = Pen::findOrFail($data['pen_id']);

            $birth = Birth::create([
                'sow_id' => $sow?->id,
                'farrowed_at' => $data['farrowed_at'] ?? now()->toDateString(),
                'total_born' => $total,
                'born_alive' => $bornAlive,
                'born_dead' => $bornDead,
                'mummified' => $mummified,
                'avg_weight' => $data['avg_weight'] ?? null,
                'pen_id' => $pen->id,
                'assistant' => $data['assistant'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Auto-registrasi piglet hidup (Q6: individu langsung)
            for ($i = 0; $i < $bornAlive; $i++) {
                $sowCode = $sow?->code ? 'anak-'.$sow->code : 'induk_tidak_diketahui';
                Pig::create([
                    'code' => CodeGenerator::pigUnique($pen->branch),
                    'sex' => $data['piglet_sex'] ?? 'jantan',
                    'breed_id' => $sow?->breed_id,
                    'birth_date' => $birth->farrowed_at,
                    'origin_type' => 'internal',
                    'origin_ref' => $birth->id,
                    'sire_id' => null,
                    'dam_id' => $sow?->id,
                    'pen_id' => $pen->id,
                    'phase_id' => $this->pigs->phaseForAge(0)?->id,
                    'status' => 'aktif',
                    'initial_weight' => $data['avg_weight'] ?? null,
                    'notes' => 'Lahir dari '.$sowCode,
                ]);
            }

            if ($sow) {
                $sow->update(['status' => 'menyusui']);
                $this->pigs->history($sow, 'status', 'bunting', 'menyusui', 'Melahirkan');
            }

            $this->audit->record('create', 'kelahiran', $birth, null, $birth->toArray(), sensitive: true, reason: 'Kelahiran');

            $this->population->invalidate($pen);

            return $birth;
        });
    }
}
