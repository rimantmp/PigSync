<?php

namespace App\Support;

use App\Services\ReportService;
use Illuminate\Support\Carbon;

/**
 * Ubah data laporan jadi ReportColumns per tipe laporan.
 *
 * Kolom PDF & CSV dideklarasikan di sini satu kali; PDF lalu menerapkan
 * `format` sementara CSV memakai nilai mentah.
 */
final class ReportBuilder
{
    public const REPORTS = ['population', 'growth', 'deaths', 'movements', 'health'];

    public function __construct(
        private readonly ReportService $service,
    ) {}

    public function build(string $report, ReportFilters $filters): ReportColumns
    {
        return match ($report) {
            'population' => $this->population($filters),
            'growth' => $this->growth($filters),
            'deaths' => $this->deaths($filters),
            'movements' => $this->movements($filters),
            'health' => $this->health($filters),
            default => throw new \InvalidArgumentException("Laporan tidak dikenal: {$report}"),
        };
    }

    public static function title(string $report): string
    {
        return match ($report) {
            'population' => 'Laporan Populasi',
            'growth' => 'Laporan Pertumbuhan',
            'deaths' => 'Laporan Kematian',
            'movements' => 'Laporan Perpindahan',
            'health' => 'Laporan Kesehatan',
            default => 'Laporan',
        };
    }

    private function population(ReportFilters $filters): ReportColumns
    {
        $rows = $this->service->population($filters)->map(fn ($pen) => [
            'kandang' => $pen->name,
            'cabang' => $pen->branch?->name ?? '-',
            'populasi' => (int) $pen->population,
            'kapasitas' => (int) $pen->capacity,
            'persen' => $pen->capacity > 0 ? round($pen->population / $pen->capacity * 100) : 0,
        ])->all();

        return new ReportColumns([
            ['key' => 'kandang', 'label' => 'Kandang', 'width' => '30%'],
            ['key' => 'cabang', 'label' => 'Cabang', 'width' => '25%'],
            ['key' => 'populasi', 'label' => 'Populasi', 'align' => 'right', 'format' => fn ($v) => number_format((int) $v)],
            ['key' => 'kapasitas', 'label' => 'Kapasitas', 'align' => 'right', 'format' => fn ($v) => number_format((int) $v)],
            ['key' => 'persen', 'label' => '% Isi', 'align' => 'right', 'format' => fn ($v) => $v.'%'],
        ], $rows, [
            'total' => $this->sum($rows, 'populasi'),
            'total_label' => 'Total Populasi',
            'total_format' => fn ($v) => number_format((float) $v).' ekor',
        ]);
    }

    private function growth(ReportFilters $filters): ReportColumns
    {
        $rows = $this->service->growth($filters)->map(fn ($w) => [
            'tanggal' => $w->weighed_at?->toDateString(),
            'ternak' => $w->pig?->code,
            'kandang' => $w->pig?->pen?->name,
            'berat' => (float) $w->weight,
        ])->all();

        return new ReportColumns([
            ['key' => 'tanggal', 'label' => 'Tanggal', 'format' => $this->dateFormat(), 'width' => '18%'],
            ['key' => 'ternak', 'label' => 'Ternak', 'width' => '22%'],
            ['key' => 'kandang', 'label' => 'Kandang', 'width' => '30%'],
            ['key' => 'berat', 'label' => 'Berat (kg)', 'align' => 'right', 'format' => fn ($v) => number_format((float) $v, 1)],
        ], $rows);
    }

    private function deaths(ReportFilters $filters): ReportColumns
    {
        $rows = $this->service->deaths($filters)->map(fn ($d) => [
            'tanggal' => $d->died_at?->toDateString(),
            'ternak' => $d->pig?->code,
            'penyebab' => $d->cause,
            'rugi' => (float) ($d->estimated_loss ?? 0),
        ])->all();

        return new ReportColumns([
            ['key' => 'tanggal', 'label' => 'Tanggal', 'format' => $this->dateFormat(), 'width' => '18%'],
            ['key' => 'ternak', 'label' => 'Ternak', 'width' => '22%'],
            ['key' => 'penyebab', 'label' => 'Penyebab', 'width' => '35%'],
            ['key' => 'rugi', 'label' => 'Estimasi Kerugian', 'align' => 'right', 'format' => fn ($v) => 'Rp '.number_format((float) $v)],
        ], $rows, [
            'total' => $this->sum($rows, 'rugi'),
            'total_label' => 'Total Estimasi Kerugian',
            'total_format' => fn ($v) => 'Rp '.number_format((float) $v),
        ]);
    }

    private function movements(ReportFilters $filters): ReportColumns
    {
        $rows = $this->service->movements($filters)->map(fn ($m) => [
            'tanggal' => $m->moved_at?->toDateString(),
            'ternak' => $m->pig?->code,
            'dari' => $m->fromPen?->name ?? '-',
            'ke' => $m->toPen?->name,
            'alasan' => $m->reason,
        ])->all();

        return new ReportColumns([
            ['key' => 'tanggal', 'label' => 'Tanggal', 'format' => $this->dateFormat(), 'width' => '15%'],
            ['key' => 'ternak', 'label' => 'Ternak', 'width' => '18%'],
            ['key' => 'dari', 'label' => 'Dari', 'width' => '20%'],
            ['key' => 'ke', 'label' => 'Ke', 'width' => '20%'],
            ['key' => 'alasan', 'label' => 'Alasan', 'width' => '27%'],
        ], $rows);
    }

    private function health(ReportFilters $filters): ReportColumns
    {
        $rows = $this->service->health($filters)->map(fn ($r) => [
            'tanggal' => $r->checked_at?->toDateString(),
            'ternak' => $r->pig?->code,
            'diagnosis' => $r->diagnosis ?: $r->disease?->name ?: '-',
            'obat' => $r->medicine?->name ?: '-',
        ])->all();

        return new ReportColumns([
            ['key' => 'tanggal', 'label' => 'Tanggal', 'format' => $this->dateFormat(), 'width' => '18%'],
            ['key' => 'ternak', 'label' => 'Ternak', 'width' => '22%'],
            ['key' => 'diagnosis', 'label' => 'Diagnosis', 'width' => '35%'],
            ['key' => 'obat', 'label' => 'Obat', 'width' => '25%'],
        ], $rows);
    }

    /**
     * Tanggal disimpan ISO di baris agar CSV tetap bisa diurutkan sebagai teks;
     * PDF memformat ulang ke gaya Indonesia.
     */
    private function dateFormat(): \Closure
    {
        return fn ($v) => $v ? Carbon::parse($v)->format('d M Y') : '-';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function sum(array $rows, string $key): float
    {
        return (float) array_sum(array_map(fn ($r) => (float) ($r[$key] ?? 0), $rows));
    }
}
