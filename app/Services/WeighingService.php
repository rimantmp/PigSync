<?php

namespace App\Services;

use App\Models\Pig;
use App\Models\PigWeight;

class WeighingService
{
    /**
     * Catat penimbangan + hitung ADG terhadap timbangan sebelumnya.
     *
     * @return array{weight: PigWeight, adg: ?float}
     */
    public function record(Pig $pig, array $data): array
    {
        $weight = PigWeight::create([
            'pig_id' => $pig->id,
            'weighed_at' => $data['weighed_at'],
            'weight' => $data['weight'],
            'method' => $data['method'] ?? 'individu',
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        $adg = $this->adg($pig);

        return ['weight' => $weight, 'adg' => $adg];
    }

    /**
     * ADG = Δberat / Δhari dari dua timbangan terakhir.
     */
    public function adg(Pig $pig): ?float
    {
        $last = $pig->weights()->latest('weighed_at')->take(2)->get();

        if ($last->count() < 2) {
            return null;
        }

        $prev = $last->get(1);
        $curr = $last->get(0);

        $days = abs($curr->weighed_at->diffInDays($prev->weighed_at));
        if ($days === 0) {
            return null;
        }

        return round(((float) $curr->weight - (float) $prev->weight) / $days, 3);
    }
}
