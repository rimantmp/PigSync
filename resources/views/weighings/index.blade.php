<x-app-layout>
    <x-slot name="title">Penimbangan</x-slot>

    @php
        $modal = new \App\Support\FormModal('weighings');
        $pigOptions = $pigs->pluck('code', 'id')->all();

        // Payload dirakit di @php lalu dipakai sebagai string: atribut class
        // komponen dievaluasi di scope terpisah yang tidak mewarisi variabel
        // view, jadi route()/$modal di sana undefined. `alpineData()` sudah
        // mengembalikan string `JSON.parse('...')` — jangan dipanggil `()`.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Catat Penimbangan',
            'subtitle' => 'Masukkan berat terbaru untuk menghitung ADG.',
            'action' => route('weighings.store'),
            'values' => ['weighed_at' => now()->toDateString(), 'method' => 'individu'],
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <form method="GET" class="flex flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode ternak..." class="w-52" />
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>

            <x-primary-button type="button"
                x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-scale class="h-4 w-4" />
                Timbang
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Tanggal</th>
                        <th class="table-head-cell">Ternak</th>
                        <th class="table-head-cell">Kandang</th>
                        <th class="table-head-cell text-right">Berat (kg)</th>
                        <th class="table-head-cell">Metode</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($weights as $w)
                        <tr class="table-row">
                            <td class="table-cell">{{ $w->weighed_at->format('d M Y') }}</td>
                            <td class="table-cell font-medium">{{ $w->pig->code }}</td>
                            <td class="table-cell">{{ $w->pig->pen?->name ?? '—' }}</td>
                            <td class="table-cell-num font-semibold">{{ $w->weight }}</td>
                            <td class="table-cell">{{ $w->method }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada penimbangan"
                                    description="Catat berat pertama untuk mulai memantau pertumbuhan.">
                                    <x-slot:action>
                                        <x-primary-button type="button"
                                            x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-scale class="h-4 w-4" />
                                            Timbang
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $weights->links() }}
    </div>

    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        title="Catat Penimbangan"
        subtitle="ADG dihitung otomatis dari timbangan sebelumnya.">

        <form method="POST" x-on:submit="onSubmit()" action="{{ route('weighings.store') }}">
            @csrf
            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="pig_id" label="Ternak" type="select" required :options="$pigOptions" :span="2" />
                <x-form-field name="weighed_at" label="Tanggal Timbang" type="date" required />
                <x-form-field name="weight" label="Berat (kg)" type="number" required hint="Contoh: 45.5" />
                <x-form-field name="method" label="Metode" type="select"
                              :options="['individu' => 'Individu', 'kelompok' => 'Kelompok']" />
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
