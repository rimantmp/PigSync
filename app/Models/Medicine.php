<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'name', 'kind', 'unit_id', 'default_dose', 'withdrawal_days', 'auto_deduct'])]
class Medicine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'withdrawal_days' => 'integer',
            'auto_deduct' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
