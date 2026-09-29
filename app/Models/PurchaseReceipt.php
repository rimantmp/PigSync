<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['po_id', 'received_at', 'receiver_id', 'status', 'notes'])]
class PurchaseReceipt extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
