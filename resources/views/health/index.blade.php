<x-app-layout>
    <x-slot name="title">Kesehatan</x-slot>

    @php
        $modal = new \App\Support\FormModal('health');
        $pigOptions = $pigs->pluck('code', 'id')->all();
        $diseaseOptions = $diseases->pluck('name', 'id')->all();
        $medicineOptions = $medicines->pluck('name', 'id')->all();

        // Payload dirakit di @php lalu dipakai sebagai string: atribut class
        // komponen dievaluasi di scope terpisah yang tidak mewarisi variabel
        // view, jadi route()/$modal di sana undefined. `alpineData()` sudah
        // mengembalikan string `JSON.parse('...')` — jangan dipanggil `()`.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Pemeriksaan Kesehatan',
            'subtitle' => 'Status ternak berubah setelah disimpan.',
            'action' => route('health.store'),
            'values' => ['checked_at' => now()->toDateString(), 'status' => 'sehat'],
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <form method="GET" class="flex flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode ternak..." class="w-52" />
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>

            <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-beaker class="h-4 w-4" />
                Pemeriksaan
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Tanggal</th>
                        <th class="table-head-cell">Ternak</th>
                        <th class="table-head-cell">Diagnosis</th>
                        <th class="table-head-cell">Obat</th>
                        <th class="table-head-cell text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($records as $r)
                        <tr class="table-row">
                            <td class="table-cell">{{ $r->checked_at->format('d M Y') }}</td>
                            <td class="table-cell font-medium">{{ $r->pig->code }}</td>
                            <td class="table-cell">{{ $r->diagnosis ?? $r->disease?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $r->medicine?->name ?? '—' }}</td>
                            <td class="table-cell text-right">
                                @if ($r->withdrawal_until && $r->withdrawal_until->isFuture())
                                    <x-badge variant="warning" dot>Withdrawal s.d. {{ $r->withdrawal_until->format('d M') }}</x-badge>
                                @else
                                    <span class="text-xs text-neutral-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada pemeriksaan"
                                    description="Catat pemeriksaan kesehatan untuk memantau kondisi ternak.">
                                    <x-slot:action>
                                        <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-beaker class="h-4 w-4" />
                                            Pemeriksaan
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links() }}
    </div>

    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        title="Pemeriksaan Kesehatan"
        subtitle="Status ternak berubah setelah disimpan.">

        <form method="POST" x-on:submit="onSubmit()" action="{{ route('health.store') }}">
            @csrf
            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="pig_id" label="Ternak" type="select" required :options="$pigOptions" :span="2" />
                <x-form-field name="checked_at" label="Tanggal" type="date" required />
                <x-form-field name="status" label="Status Ternak" type="select" required
                              :options="['sakit' => 'Sakit', 'karantina' => 'Karantina']" />
                <x-form-field name="symptoms" label="Gejala" type="textarea" :span="2" />
                <x-form-field name="disease_id" label="Penyakit" type="select" :options="$diseaseOptions" />
                <x-form-field name="medicine_id" label="Obat / Vaksin" type="select" :options="$medicineOptions" />
                <x-form-field name="dose" label="Dosis" hint="Contoh: 2 ml" />
                <x-form-field name="route" label="Cara"
                              :options="['injeksi' => 'Injeksi', 'oral' => 'Oral', 'topikal' => 'Topikal']" />
                <x-form-field name="vet" label="Nama Vet" />
                <x-form-field name="diagnosis" label="Diagnosis" :span="2" />
                <x-form-field name="notes" label="Catatan" type="textarea" :span="2" />
            </div>

            <div class="mt-6 flex items-center gap-3 border-t border-neutral-100 pt-4">
                <x-primary-button type="submit" x-bind:disabled="saving">
                    <span x-show="!saving">Simpan</span>
                    <span x-show="saving" class="inline-flex items-center gap-2" x-cloak>
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Menyimpan…
                    </span>
                </x-primary-button>

                <x-secondary-button type="button" x-on:click="onClose()">Batal</x-secondary-button>
            </div>
        </form>
    </x-form-modal>
</x-app-layout>
