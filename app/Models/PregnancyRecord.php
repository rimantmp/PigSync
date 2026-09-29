<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['breeding_id', 'sow_id', 'checked_at', 'result', 'method', 'expected_farrow_at', 'status'])]
class PregnancyRecord extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checked_at' => 'date',
            'expected_farrow_at' => 'date',
        ];
    }

    public function breeding(): BelongsTo
    {
        return $this->belongsTo(BreedingRecord::class);
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }
}
