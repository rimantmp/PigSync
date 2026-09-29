<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Pembungkus dompdf.
 *
 * Centralisasi setting paper/orientation supaya controller tidak perlu
 * mengingat detail dompdf. dompdf tidak mendukung flexbox, grid, atau
 * aset dari Vite, jadi view PDF wajib memakai layout pdf/*.
 */
class PdfService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function landscape(string $view, array $data, string $filename): Response
    {
        return $this->render($view, $data, $filename, 'landscape');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function portrait(string $view, array $data, string $filename): Response
    {
        return $this->render($view, $data, $filename, 'portrait');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function render(string $view, array $data, string $filename, string $orientation): Response
    {
        $pdf = Pdf::loadView($view, array_merge($data, ['orientation' => $orientation]));

        $pdf->setPaper('a4', $orientation);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download($filename);
    }
}
