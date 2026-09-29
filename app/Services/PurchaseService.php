<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly AuditService $audit,
    ) {}

    /**
     * Buat purchase request.
     *
     * @param  array<int, array{item_type:string, item_id:int, qty:float, unit_id?:int, est_price?:float}>  $items
     */
    public function createRequest(int $branchId, array $items, array $meta = []): PurchaseRequest
    {
        return DB::transaction(function () use ($branchId, $items, $meta) {
            $pr = PurchaseRequest::create([
                'branch_id' => $branchId,
                'request_date' => $meta['request_date'] ?? now()->toDateString(),
                'requester_id' => auth()->id(),
                'status' => $meta['status'] ?? 'draft',
                'notes' => $meta['notes'] ?? null,
            ]);

            foreach ($items as $line) {
                PurchaseRequestItem::create([
                    'pr_id' => $pr->id,
                    'item_type' => $line['item_type'],
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'unit_id' => $line['unit_id'] ?? null,
                    'est_price' => $line['est_price'] ?? null,
                ]);
            }

            return $pr;
        });
    }

    /**
     * Konversi PR -> PO (BR: PR bisa diskip pembelian kecil — Q5, default: butuh PR).
     */
    public function createOrderFromRequest(PurchaseRequest $pr, int $supplierId, array $meta = []): PurchaseOrder
    {
        throw_if($pr->status !== 'approved', \DomainException::class, 'PR belum disetujui.');

        return DB::transaction(function () use ($pr, $supplierId, $meta) {
            $items = $pr->items->map(fn ($item) => [
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'qty' => $item->qty,
                'unit_id' => $item->unit_id,
                'price' => $item->est_price ?? $meta['price'] ?? 0,
            ])->all();

            // Harga PR adalah estimasi; wajib diisi harga riil saat dimasukkan PO.
            throw_if(
                collect($items)->contains(fn ($i) => (float) $i['price'] <= 0),
                \DomainException::class,
                'Harga item wajib diisi (>0) saat membuat PO.'
            );

            $po = $this->createOrder($pr->branch_id, $supplierId, $pr->id, $items, $meta);

            $pr->update(['status' => 'po_created']);

            return $po;
        });
    }

    /**
     * Buat PO langsung (bypass PR untuk pembelian kecil).
     *
     * @param  array<int, array{item_type:string, item_id:int, qty:float, unit_id?:int, price:float}>  $items
     */
    public function createOrder(int $branchId, int $supplierId, ?int $prId, array $items, array $meta = []): PurchaseOrder
    {
        return DB::transaction(function () use ($branchId, $supplierId, $prId, $items, $meta) {
            $total = 0.0;

            $po = PurchaseOrder::create([
                'branch_id' => $branchId,
                'supplier_id' => $supplierId,
                'pr_id' => $prId,
                'po_date' => $meta['po_date'] ?? now()->toDateString(),
                'po_number' => $meta['po_number'] ?? 'PO-'.Str::upper(Str::random(8)),
                'status' => 'draft',
                'total' => 0,
                'payment_method' => $meta['payment_method'] ?? 'transfer',
                'due_date' => $meta['due_date'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);

            foreach ($items as $line) {
                $subtotal = (float) $line['qty'] * (float) $line['price'];
                $total += $subtotal;

                PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'item_type' => $line['item_type'],
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'unit_id' => $line['unit_id'] ?? null,
                    'price' => $line['price'],
                    'subtotal' => $subtotal,
                    'received_qty' => 0,
                ]);
            }

            $po->update(['total' => $total]);

            return $po;
        });
    }

    /**
     * Terima barang -> naikkan stok (BR: tidak boleh melebihi PO).
     */
    public function receive(PurchaseOrder $po, array $meta = []): PurchaseReceipt
    {
        return DB::transaction(function () use ($po, $meta) {
            $receipt = PurchaseReceipt::create([
                'po_id' => $po->id,
                'received_at' => $meta['received_at'] ?? now()->toDateString(),
                'receiver_id' => auth()->id(),
                'status' => 'diterima',
                'notes' => $meta['notes'] ?? null,
            ]);

            $warehouse = Warehouse::firstOrCreate(
                ['branch_id' => $po->branch_id],
                ['code' => 'WH-'.$po->branch_id, 'name' => 'Gudang Cabang '.$po->branch_id]
            );

            foreach ($po->items as $item) {
                $receiveQty = $meta['received_qty'][$item->id] ?? $item->qty;

                // Bandingkan dengan sisa outstanding, bukan qty PO: PO bisa diterima
                // beberapa kali, dan received_qty menumpuk antar penerimaan.
                throw_if(
                    (float) $item->received_qty + (float) $receiveQty > (float) $item->qty,
                    \DomainException::class,
                    'Jumlah terima melebihi PO.'
                );

                PurchaseReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'po_item_id' => $item->id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'qty' => $receiveQty,
                    'unit_id' => $item->unit_id,
                ]);

                $this->stocks->receive($warehouse, $item->item_type, $item->item_id, (float) $receiveQty, [
                    'unit_id' => $item->unit_id,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $po->id,
                ]);

                $item->update(['received_qty' => (float) $item->received_qty + (float) $receiveQty]);
            }

            $po->update(['status' => 'received']);

            $this->audit->record('create', 'pembelian', $receipt, null, $receipt->toArray(), sensitive: true, reason: 'Penerimaan barang');

            return $receipt;
        });
    }

    /**
     * Buat invoice untuk PO.
     */
    public function invoice(PurchaseOrder $po, array $meta = []): PurchaseInvoice
    {
        return DB::transaction(function () use ($po, $meta) {
            $invoice = PurchaseInvoice::create([
                'po_id' => $po->id,
                'invoice_number' => $meta['invoice_number'] ?? 'INV-'.Str::upper(Str::random(8)),
                'invoice_date' => $meta['invoice_date'] ?? now()->toDateString(),
                'total' => $meta['total'] ?? $po->total,
                'status' => 'belum_bayar',
            ]);

            $po->update(['status' => 'invoiced']);

            return $invoice;
        });
    }
}
