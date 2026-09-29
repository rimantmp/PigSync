<?php

namespace App\Rules;

use App\Support\ItemCatalog;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Pastikan item_id benar-benar ada di master sesuai item_type.
 *
 * Tanpa ini user bisa mengirim item_id yang tidak ada dan sistem tetap
 * menyimpannya, sehingga stok menunjuk ke barang yang tidak pernah ada.
 */
class ItemExists implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = $this->siblingType($attribute);

        if (! in_array($type, ItemCatalog::types(), true)) {
            $fail('Jenis item tidak dikenal.');

            return;
        }

        if (! ItemCatalog::modelFor($type)::whereKey((int) $value)->exists()) {
            $fail('Item yang dipilih tidak ada di master '.ItemCatalog::labelFor($type).'.');
        }
    }

    /**
     * Ambil item_type dari baris yang sama, mis. "items.0.item_type"
     * untuk atribut "items.0.item_id".
     */
    private function siblingType(string $attribute): ?string
    {
        $position = strrpos($attribute, '.');

        if ($position === false) {
            return null;
        }

        $type = data_get($this->data, substr($attribute, 0, $position).'.item_type');

        return is_string($type) ? $type : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
