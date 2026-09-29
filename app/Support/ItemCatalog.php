<?php

namespace App\Support;

use App\Models\Equipment;
use App\Models\FeedType;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Resolver nama item untuk kolom polimorfik item_type + item_id.
 *
 * Disimpan di satu tempat karena enam model menyimpan pasangan yang sama
 * (PurchaseOrderItem, PurchaseRequestItem, Stock, StockTransaction,
 * StockOpnameItem, PurchaseReceiptItem).
 */
final class ItemCatalog
{
    /**
     * @return class-string<Model>
     */
    public static function modelFor(string $itemType): string
    {
        return match ($itemType) {
            'feed' => FeedType::class,
            'medicine' => Medicine::class,
            'equipment' => Equipment::class,
            default => throw new InvalidArgumentException("item_type tidak dikenal: {$itemType}"),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function types(): array
    {
        return ['feed', 'medicine', 'equipment'];
    }

    public static function labelFor(string $itemType): string
    {
        return match ($itemType) {
            'feed' => 'Pakan',
            'medicine' => 'Obat/Vaksin',
            'equipment' => 'Perlengkapan',
            default => $itemType,
        };
    }

    public static function nameFor(string $itemType, int $itemId): string
    {
        return self::namesFor([['item_type' => $itemType, 'item_id' => $itemId]])[self::key($itemType, $itemId)]
            ?? self::labelFor($itemType).' #'.$itemId;
    }

    /**
     * Kunci komposit "tipe:id" — item_id saja bentrok antar tipe
     * (pakan #1 dan obat #1 adalah barang berbeda).
     */
    public static function key(string $itemType, int|string $itemId): string
    {
        return $itemType.':'.(int) $itemId;
    }

    /**
     * Batch: satu query per tipe item, bukan satu per baris.
     *
     * @param  iterable<array{item_type: string, item_id: int|string}>  $lines
     * @return array<string, string> nama keyed "tipe:id"
     */
    public static function namesFor(iterable $lines): array
    {
        $byType = [];

        foreach ($lines as $line) {
            $byType[$line['item_type']][] = (int) $line['item_id'];
        }

        $names = [];

        foreach ($byType as $type => $ids) {
            $model = self::modelFor($type);

            foreach ($model::whereIn('id', $ids)->pluck('name', 'id') as $id => $name) {
                $names[self::key($type, (int) $id)] = $name;
            }
        }

        return $names;
    }

    /**
     * Opsi select untuk form — dikelompokkan per tipe item.
     *
     * @return array<string, Collection<int, string>>
     */
    public static function options(): array
    {
        $options = [
            'feed' => FeedType::orderBy('name')->pluck('name', 'id'),
            'medicine' => Medicine::orderBy('name')->pluck('name', 'id'),
            'equipment' => Equipment::orderBy('name')->pluck('name', 'id'),
        ];

        return $options;
    }
}
