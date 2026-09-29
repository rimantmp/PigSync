<?php

namespace App\Services;

use App\Models\Pig;
use App\Models\Revenue;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(
        private readonly PigService $pigs,
        private readonly PopulationService $population,
        private readonly AuditService $audit,
    ) {}

    /**
     * Jual babi — BR-03 (tidak double), cek withdrawal, kurangi populasi,
     * catat pendapatan (BR-16 turunan).
     *
     * @param  array<int, array{pig_id:int, weight:float, price_per_kg:float}>  $items
     */
    public function sell(int $branchId, int $customerId, array $items, array $meta = []): Sale
    {
        return DB::transaction(function () use ($branchId, $customerId, $items, $meta) {
            $total = 0.0;

            $payload = [];
            foreach ($items as $line) {
                $pig = Pig::findOrFail($line['pig_id']);

                throw_if($pig->status === 'dijual', \DomainException::class, "Ternak {$pig->code} sudah terjual.");
                throw_if($pig->status === 'mati', \DomainException::class, "Ternak {$pig->code} sudah mati.");

                // Withdrawal: larangan jual bila masih dalam masa obat
                $withdrawal = $pig->healthRecords()->whereDate('withdrawal_until', '>=', now())->exists();
                throw_if($withdrawal, \DomainException::class, "Ternak {$pig->code} dalam masa larangan obat.");

                $subtotal = (float) $line['weight'] * (float) $line['price_per_kg'];
                $total += $subtotal;

                $payload[] = [
                    'pig' => $pig,
                    'weight' => $line['weight'],
                    'price_per_kg' => $line['price_per_kg'],
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'sale_date' => $meta['sale_date'] ?? now()->toDateString(),
                'invoice_number' => $meta['invoice_number'] ?? 'INV-'.Str::upper(Str::random(8)),
                'total' => $total,
                'payment_status' => $meta['payment_status'] ?? 'belum_bayar',
                'notes' => $meta['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($payload as $line) {
                $pig = $line['pig'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'pig_id' => $pig->id,
                    'weight' => $line['weight'],
                    'price_per_kg' => $line['price_per_kg'],
                    'subtotal' => $line['subtotal'],
                ]);

                $pen = $pig->pen;
                $pig->update(['status' => 'dijual', 'sold_at' => now()]);
                $this->pigs->history($pig, 'penjualan', 'aktif', 'dijual', 'Terjual: '.$sale->invoice_number);

                if ($pen) {
                    $this->population->invalidate($pen);
                }
            }

            if (($meta['payment_status'] ?? '') === 'lunas') {
                Revenue::create([
                    'branch_id' => $branchId,
                    'source' => 'penjualan',
                    'amount' => $total,
                    'received_at' => $meta['sale_date'] ?? now()->toDateString(),
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $this->audit->record('create', 'penjualan', $sale, null, $sale->toArray(), sensitive: true, reason: 'Penjualan');

            return $sale;
        });
    }
}
