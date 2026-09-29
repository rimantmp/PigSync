<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sow_id', 'farrowed_at', 'total_born', 'born_alive', 'born_dead', 'mummified', 'avg_weight', 'pen_id', 'assistant', 'notes', 'user_id'])]
class Birth extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'farrowed_at' => 'date',
            'total_born' => 'integer',
            'born_alive' => 'integer',
            'born_dead' => 'integer',
            'mummified' => 'integer',
            'avg_weight' => 'decimal:2',
        ];
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function pen(): BelongsTo
    {
        return $this->belongsTo(Pen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Piglet hasil kelahiran ini.
     */
    public function piglets()
    {
        return Pig::where('origin_type', 'internal')->where('origin_ref', $this->id);
    }
}
