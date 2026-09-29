<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pig_id', 'died_at', 'pen_id', 'cause', 'suspected_disease_id', 'disposal', 'estimated_loss', 'photo', 'verified_by', 'user_id', 'notes'])]
class Death extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'died_at' => 'date',
            'estimated_loss' => 'decimal:2',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function pen(): BelongsTo
    {
        return $this->belongsTo(Pen::class);
    }

    public function suspectedDisease(): BelongsTo
    {
        return $this->belongsTo(Disease::class, 'suspected_disease_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
