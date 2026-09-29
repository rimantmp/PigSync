<x-app-layout>
    <x-slot name="title">Kelahiran</x-slot>

    @php
        $modal = new \App\Support\FormModal('births');
        $sowOptions = $sows->pluck('code', 'id')->all();
        $penOptions = $pens->pluck('name', 'id')->all();

        // Payload dirakit di @php lalu dipakai sebagai string: atribut class
        // komponen dievaluasi di scope terpisah yang tidak mewarisi variabel
        // view, jadi route()/$modal di sana undefined. `alpineData()` sudah
        // mengembalikan string `JSON.parse('...')` — jangan dipanggil `()`.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Catat Kelahiran',
            'subtitle' => 'Piglet hidup otomatis didaftarkan sebagai ternak baru.',
            'action' => route('births.store'),
            'values' => ['farrowed_at' => now()->toDateString()],
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <form method="GET" class="flex flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode induk..." class="w-52" />
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>

            <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-sparkles class="h-4 w-4" />
                Catat Kelahiran
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Tanggal</th>
                        <th class="table-head-cell">Induk</th>
                        <th class="table-head-cell">Kandang</th>
                        <th class="table-head-cell text-right">Total</th>
                        <th class="table-head-cell text-right">Hidup</th>
                        <th class="table-head-cell text-right">Mati</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($births as $b)
                        <tr class="table-row">
                            <td class="table-cell">{{ $b->farrowed_at->format('d M Y') }}</td>
                            <td class="table-cell font-medium">{{ $b->sow?->code ?? '—' }}</td>
                            <td class="table-cell">{{ $b->pen?->name ?? '—' }}</td>
                            <td class="table-cell-num">{{ $b->total_born }}</td>
                            <td class="table-cell-num font-semibold text-success-600">{{ $b->born_alive }}</td>
                            <td class="table-cell-num text-error-600">{{ $b->born_dead + $b->mummified }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada kelahiran"
                                    description="Catat kelahiran untuk mendaftarkan piglet sebagai ternak baru.">
                                    <x-slot:action>
                                        <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-sparkles class="h-4 w-4" />
                                            Catat Kelahiran
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $births->links() }}
    </div>

    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        title="Catat Kelahiran"
        subtitle="Piglet hidup otomatis didaftarkan sebagai ternak baru.">

        <form method="POST" x-on:submit="onSubmit()" action="{{ route('births.store') }}">
            @csrf
            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="sow_id" label="Induk (kosongkan bila tidak diketahui)" type="select" :options="$sowOptions" :span="2" />
                <x-form-field name="pen_id" label="Kandang" type="select" required :options="$penOptions" />
                <x-form-field name="farrowed_at" label="Tanggal" type="date" required />
                <x-form-field name="born_alive" label="Lahir Hidup" type="number" required />
                <x-form-field name="born_dead" label="Lahir Mati" type="number" />
                <x-form-field name="mummified" label="Mummy" type="number" />
                <x-form-field name="avg_weight" label="Berat rata-rata (kg)" type="number" />
                <x-form-field name="assistant" label="Pendamping" :span="2" />
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
