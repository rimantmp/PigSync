<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pig_id', 'checked_at', 'symptoms', 'disease_id', 'diagnosis', 'medicine_id', 'dose', 'route', 'withdrawal_until', 'vet', 'user_id', 'notes'])]
class HealthRecord extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checked_at' => 'date',
            'withdrawal_until' => 'date',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
