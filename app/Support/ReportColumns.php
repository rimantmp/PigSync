<?php

namespace App\Support;

/**
 * Definisi kolom laporan.
 *
 * Header dideklarasikan sekali lalu dipakai bersama oleh CSV dan PDF supaya
 * kolom tidak pernah berbeda antara file dan tampilan. Nilai sel sengaja
 * disimpan mentah: CSV memakai angka apa adanya (agar bisa diurutkan Excel),
 * PDF memformat sendiri lewat kolom `format`.
 *
 * @phpstan-type Column array{key:string, label:string, align?:'left'|'right'|'center', format?:callable, width?:string}
 */
final class ReportColumns
{
    /**
     * @param  array<int, array{key:string, label:string, align?:'left'|'right'|'center', format?:callable, width?:string}>  $columns
     * @param  iterable<array<string, mixed>>  $rows
     * @param  array<string, string>  $meta
     */
    public function __construct(
        public readonly array $columns,
        public readonly iterable $rows,
        public readonly array $meta = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function labels(): array
    {
        return array_map(fn (array $c) => $c['label'], $this->columns);
    }

    /**
     * Baris CSV — nilai mentah tanpa formatting.
     *
     * @return iterable<array<int, scalar|null>>
     */
    public function csvRows(): iterable
    {
        foreach ($this->rows as $row) {
            yield array_map(
                fn (array $c) => $row[$c['key']] ?? null,
                $this->columns
            );
        }
    }
}
