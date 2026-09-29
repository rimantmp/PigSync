<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'receipt_id');
    }
}
