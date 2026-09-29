<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvDownload
{
    /**
     * Unduh CSV UTF-8 dengan BOM.
     *
     * BOM wajib: tanpa itu Excel membaca karakter non-ASCII sebagai karakter
     * aneh saat file dibuka.
     *
     * @param  array<int, string>  $headers
     * @param  iterable<array<int, scalar|null>>  $rows
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');

            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");

            // Escape dikosongkan: default fputcsv merusak nilai yang mengandung
            // backslash (nama supplier/kandang) dan deprecated di PHP 8.4.
            fputcsv($out, $headers, ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($out, array_values($row), ',', '"', '');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
