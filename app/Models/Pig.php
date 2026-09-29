<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code', 'tag_id', 'rfid', 'sex', 'breed_id', 'birth_date', 'origin_type', 'origin_ref',
    'sire_id', 'dam_id', 'pen_id', 'phase_id', 'status', 'initial_weight', 'photo', 'notes',
    'sold_at', 'died_at',
])]
class Pig extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'initial_weight' => 'decimal:2',
            'sold_at' => 'datetime',
            'died_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function pen(): BelongsTo
    {
        return $this->belongsTo(Pen::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(PigBreed::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(PigPhase::class);
    }

    public function sire(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sire_id');
    }

    public function dam(): BelongsTo
    {
        return $this->belongsTo(self::class, 'dam_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PigHistory::class);
    }

    public function weights(): HasMany
    {
        return $this->hasMany(PigWeight::class)->orderBy('weighed_at');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PigMovement::class)->orderBy('moved_at');
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class)->orderBy('checked_at');
    }

    public function deaths(): HasMany
    {
        return $this->hasMany(Death::class);
    }

    /**
     * Cabang babi diturunkan dari kandangnya.
     */
    public function branchId(): ?int
    {
        return $this->pen?->branch_id;
    }

    /**
     * Babi dianggap hidup bila bukan mati/dijual/afkir.
     */
    public function isAlive(): bool
    {
        return $this->status !== 'mati';
    }
}
