<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pig_id', 'weighed_at', 'weight', 'method', 'notes', 'user_id'])]
class PigWeight extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weighed_at' => 'date',
            'weight' => 'decimal:2',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
