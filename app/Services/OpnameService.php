<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class OpnameService
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * Lakukan stock opname — simpan selisih + alasan (BR dari §22 kriteria 11).
     *
     * @param  array<int, array{item_type:string, item_id:int, actual_qty:float, reason?:string}>  $items
     */
    public function run(Warehouse $warehouse, array $items, array $meta = []): StockOpname
    {
        return DB::transaction(function () use ($warehouse, $items, $meta) {
            $opname = StockOpname::create([
                'warehouse_id' => $warehouse->id,
                'opname_date' => $meta['opname_date'] ?? now()->toDateString(),
                'status' => 'selesai',
                'notes' => $meta['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($items as $line) {
                $systemQty = Stock::where('warehouse_id', $warehouse->id)
                    ->where('item_type', $line['item_type'])
                    ->where('item_id', $line['item_id'])
                    ->value('qty') ?? 0;

                $actual = (float) $line['actual_qty'];
                $diff = $actual - (float) $systemQty;

                throw_if($diff != 0 && blank($line['reason'] ?? null), \DomainException::class, 'Selisih ≠ 0 wajib diisi alasan.');

                StockOpnameItem::create([
                    'opname_id' => $opname->id,
                    'item_type' => $line['item_type'],
                    'item_id' => $line['item_id'],
                    'system_qty' => $systemQty,
                    'actual_qty' => $actual,
                    'diff' => $diff,
                    'reason' => $line['reason'] ?? null,
                ]);

                // Sesuaikan stok ke angka fisik
                Stock::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'item_type' => $line['item_type'], 'item_id' => $line['item_id']],
                    ['qty' => $actual]
                );
            }

            $this->audit->record('create', 'opname', $opname, null, $opname->toArray(), sensitive: true, reason: 'Stock opname');

            return $opname;
        });
    }
}
