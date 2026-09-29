<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * Terima stok masuk.
     */
    public function receive(Warehouse $warehouse, string $itemType, int $itemId, float $qty, array $meta = []): Stock
    {
        return DB::transaction(function () use ($warehouse, $itemType, $itemId, $qty, $meta) {
            $stock = $this->stockRow($warehouse, $itemType, $itemId, $meta);

            $before = $stock->qty;
            $stock->increment('qty', $qty);

            $this->transact($warehouse, $itemType, $itemId, 'in', $qty, 'receipt', $meta);

            $this->audit->record('create', 'gudang', $stock, ['qty' => $before], ['qty' => $stock->qty]);

            return $stock;
        });
    }

    /**
     * Keluar stok — stok tak negatif (BR-09), pengeluaran wajib tujuan (BR-10).
     */
    public function issue(Warehouse $warehouse, string $itemType, int $itemId, float $qty, array $meta = []): Stock
    {
        throw_if(
            blank($meta['pen_id'] ?? $meta['pig_id'] ?? $meta['notes'] ?? null),
            \DomainException::class,
            'Pengeluaran stok harus menyebut tujuan (BR-10).'
        );

        return DB::transaction(function () use ($warehouse, $itemType, $itemId, $qty, $meta) {
            $stock = $this->stockRow($warehouse, $itemType, $itemId, $meta);

            throw_if(
                (float) $stock->qty < $qty,
                \DomainException::class,
                "Stok tidak cukup (tersedia {$stock->qty})."
            );

            $before = $stock->qty;
            $stock->decrement('qty', $qty);

            $this->transact($warehouse, $itemType, $itemId, 'out', $qty, $meta['txn_type'] ?? 'issue', $meta);

            $this->audit->record('create', 'gudang', $stock, ['qty' => $before], ['qty' => $stock->qty]);

            return $stock;
        });
    }

    /**
     * Ketersediaan stok item di gudang.
     */
    public function available(Warehouse $warehouse, string $itemType, int $itemId): float
    {
        return (float) Stock::where('warehouse_id', $warehouse->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->value('qty') ?? 0;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function stockRow(Warehouse $warehouse, string $itemType, int $itemId, array $meta): Stock
    {
        return Stock::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'item_type' => $itemType, 'item_id' => $itemId],
            [
                'unit_id' => $meta['unit_id'] ?? null,
                'min_stock' => $meta['min_stock'] ?? 0,
                'expiry_date' => $meta['expiry_date'] ?? null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function transact(Warehouse $warehouse, string $itemType, int $itemId, string $direction, float $qty, string $txnType, array $meta): void
    {
        StockTransaction::create([
            'warehouse_id' => $warehouse->id,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'direction' => $direction,
            'qty' => $qty,
            'unit_id' => $meta['unit_id'] ?? null,
            'txn_type' => $txnType,
            'pen_id' => $meta['pen_id'] ?? null,
            'pig_id' => $meta['pig_id'] ?? null,
            'reference_type' => $meta['reference_type'] ?? null,
            'reference_id' => $meta['reference_id'] ?? null,
            'user_id' => auth()->id(),
            'notes' => $meta['notes'] ?? null,
            'at' => now(),
        ]);
    }
}
