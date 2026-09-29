<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sow_id', 'boar_id', 'bred_at', 'method', 'technician', 'dose', 'notes', 'user_id'])]
class BreedingRecord extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bred_at' => 'date',
        ];
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function boar(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
