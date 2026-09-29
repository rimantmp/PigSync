<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'name', 'category', 'unit_id', 'default_price'])]
class Equipment extends Model
{
    use HasFactory;

    // "equipment" kata tak berjamak dalam pluralizer Laravel, jadi tabelnya
    // ikut jadi "equipment". Disetel eksplisit supaya tidak bergantung
    // pada perilaku pluralizer.
    protected $table = 'equipments';

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
