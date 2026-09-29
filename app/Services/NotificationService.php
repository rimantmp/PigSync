<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Pen;
use App\Models\Stock;

class NotificationService
{
    /**
     * Kirim notifikasi ke user (null = broadcast role).
     *
     * @param  array<int>|null  $userIds
     */
    public function notify(?int $userId, string $type, string $severity, string $title, ?string $body = null, ?string $link = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);
    }

    /**
     * Periksa stok minimum & buat alert bila di bawah ambang.
     */
    public function checkStockMinimum(): void
    {
        $stocks = Stock::whereColumn('qty', '<=', 'min_stock')->get();

        foreach ($stocks as $stock) {
            $this->notify(null, 'stok_minimum', 'warning', "Stok {$stock->item_type}#{$stock->item_id} di bawah minimum ({$stock->qty}).");
        }
    }

    /**
     * Alert ternak dalam masa withdrawal mendekati selesai / kandang mendekati kapasitas.
     */
    public function checkPenCapacity(): void
    {
        $pens = Pen::all();
        $population = app(PopulationService::class);

        foreach ($pens as $pen) {
            if ($pen->capacity <= 0) {
                continue;
            }

            $pop = $population->penPopulation($pen);
            $pct = $pop / $pen->capacity * 100;

            if ($pct >= 90) {
                $this->notify(null, 'kapasitas', 'warning', "Kandang {$pen->name} terisi {$pct}%.");
            }
        }
    }
}
