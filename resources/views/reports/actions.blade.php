{{-- Aksi bawah laporan: kembali + export CSV/PDF dengan filter yang sama. --}}
<div class="flex items-center gap-3 mt-3">
    <a href="{{ route('reports.index') }}" class="inline-block text-sm text-slate-600">← Kembali</a>
    <a href="{{ route('reports.export.csv', array_merge(['report' => $report], request()->only(['branch_id', 'from', 'to']))) }}"
       class="inline-block text-sm text-emerald-600 hover:text-emerald-800">⬇ Download CSV</a>
    <a href="{{ route('reports.export.pdf', array_merge(['report' => $report], request()->only(['branch_id', 'from', 'to']))) }}"
       class="inline-block text-sm text-rose-600 hover:text-rose-800">⬇ Download PDF</a>
</div>
