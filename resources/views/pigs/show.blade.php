<x-app-layout>
    <x-slot name="title">Kartu Ternak {{ $pig->code }}</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-900">{{ $pig->code }}</h2>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ statusBadge($pig->status) }}">{{ $pig->status }}</span>
            </div>
            <a href="{{ route('pigs.edit', $pig) }}" class="text-sm text-slate-600 hover:text-slate-900">Edit</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-5 space-y-3">
                <h3 class="font-semibold text-gray-700 border-b pb-2">Identitas</h3>
                <dl class="text-sm space-y-2">
                    @foreach ([
                        'Jenis Kelamin' => $pig->sex,
                        'Ras' => $pig->breed?->name ?? '-',
                        'Ear Tag' => $pig->tag_id ?? '-',
                        'Tanggal Lahir' => $pig->birth_date?->format('d M Y'),
                        'Asal' => $pig->origin_type,
                        'Induk (dam)' => $pig->dam?->code ?? '-',
                        'Pejantan (sire)' => $pig->sire?->code ?? '-',
                    ] as $k => $v)
                        <div class="flex justify-between"><dt class="text-gray-500">{{ $k }}</dt><dd class="font-medium">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow p-5 space-y-3">
                <h3 class="font-semibold text-gray-700 border-b pb-2">Lokasi</h3>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500">Kandang</dt><dd class="font-medium">{{ $pig->pen?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Cabang</dt><dd class="font-medium">{{ $pig->pen?->branch?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Fase</dt><dd class="font-medium">{{ $pig->phase?->name ?? '-' }}</dd></div>
                </dl>

                <h3 class="font-semibold text-gray-700 border-b pb-2 mt-4">Riwayat Perpindahan</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($pig->movements as $m)
                        <li class="text-gray-600">{{ $m->moved_at->format('d M Y') }}: {{ $m->fromPen?->name ?? '—' }} → {{ $m->toPen?->name }} ({{ $m->reason }})</li>
                    @empty
                        <li class="text-gray-400">Belum ada perpindahan.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow p-5 space-y-3">
                <h3 class="font-semibold text-gray-700 border-b pb-2">Pertumbuhan</h3>
                <table class="w-full text-sm">
                    <thead class="text-gray-500"><tr><th class="text-left py-1">Tanggal</th><th class="text-right py-1">Berat (kg)</th></tr></thead>
                    <tbody>
                        @forelse ($pig->weights as $w)
                            <tr class="border-t border-gray-100"><td class="py-1">{{ $w->weighed_at->format('d M Y') }}</td><td class="py-1 text-right font-medium">{{ $w->weight }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-2 text-gray-400">Belum ada penimbangan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Riwayat Kesehatan</h3>
            <table class="w-full text-sm mt-2">
                <thead class="text-gray-500"><tr>
                    <th class="text-left py-1">Tanggal</th><th class="text-left py-1">Diagnosis</th><th class="text-left py-1">Obat</th><th class="text-left py-1">Withdrawal s.d.</th>
                </tr></thead>
                <tbody>
                    @forelse ($pig->healthRecords as $h)
                        <tr class="border-t border-gray-100">
                            <td class="py-1">{{ $h->checked_at->format('d M Y') }}</td>
                            <td class="py-1">{{ $h->diagnosis ?? $h->disease?->name ?? '-' }}</td>
                            <td class="py-1">{{ $h->medicine?->name ?? '-' }}</td>
                            <td class="py-1">{{ $h->withdrawal_until?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-2 text-gray-400">Riwayat kesehatan kosong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Riwayat Status</h3>
            <ul class="text-sm space-y-1 mt-2">
                @forelse ($pig->histories as $h)
                    <li class="text-gray-600">{{ $h->event_date->format('d M Y') }} — {{ $h->event_type }}: {{ $h->from_value ?? '—' }} → {{ $h->to_value }} @if($h->notes) <span class="text-gray-400">({{ $h->notes }})</span> @endif</li>
                @empty
                    <li class="text-gray-400">Belum ada riwayat.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>