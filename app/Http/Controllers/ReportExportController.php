<?php

namespace App\Http\Controllers;

use App\Services\PdfService;
use App\Support\CsvDownload;
use App\Support\ReportBuilder;
use App\Support\ReportColumns;
use App\Support\ReportFilters;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        private readonly ReportBuilder $builder,
        private readonly PdfService $pdf,
    ) {}

    public function csv(Request $request, string $report): StreamedResponse
    {
        [$table, $filters] = $this->table($request, $report);

        return CsvDownload::stream(
            $this->filename($report, 'csv'),
            $table->labels(),
            $table->csvRows()
        );
    }

    public function pdf(Request $request, string $report)
    {
        [$table, $filters] = $this->table($request, $report);

        return $this->pdf->landscape('pdf.report', [
            'title' => ReportBuilder::title($report),
            'table' => $table,
            'filters' => $filters,
        ], $this->filename($report, 'pdf'));
    }

    /**
     * @return array{0: ReportColumns, 1: ReportFilters}
     */
    private function table(Request $request, string $report): array
    {
        abort_unless(
            in_array($report, ReportBuilder::REPORTS, true),
            404,
            'Laporan tidak dikenal.'
        );

        $filters = ReportFilters::fromRequest($request);

        return [$this->builder->build($report, $filters), $filters];
    }

    private function filename(string $report, string $extension): string
    {
        return 'laporan-'.$report.'-'.now()->format('Ymd-His').'.'.$extension;
    }
}
