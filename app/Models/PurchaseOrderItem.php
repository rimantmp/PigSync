<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['po_id', 'item_type', 'item_id', 'qty', 'unit_id', 'price', 'subtotal', 'received_qty'])]
class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'received_qty' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
