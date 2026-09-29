<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pen_id', 'report_date', 'population_count', 'sick_count', 'dead_count', 'notes', 'user_id'])]
class DailyReport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'population_count' => 'integer',
            'sick_count' => 'integer',
            'dead_count' => 'integer',
        ];
    }

    public function pen(): BelongsTo
    {
        return $this->belongsTo(Pen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
