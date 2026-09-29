<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pig_id', 'from_pen_id', 'to_pen_id', 'moved_at', 'reason', 'type', 'user_id', 'notes'])]
class PigMovement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'moved_at' => 'date',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function fromPen(): BelongsTo
    {
        return $this->belongsTo(Pen::class, 'from_pen_id');
    }

    public function toPen(): BelongsTo
    {
        return $this->belongsTo(Pen::class, 'to_pen_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
