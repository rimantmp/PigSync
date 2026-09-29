<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'action', 'module', 'entity_type', 'entity_id', 'before', 'after', 'reason', 'sensitive', 'ip', 'user_agent', 'at'])]
class AuditLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'sensitive' => 'boolean',
            'at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
