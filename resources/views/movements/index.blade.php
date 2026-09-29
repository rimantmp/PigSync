<x-app-layout>
    <x-slot name="title">Perpindahan</x-slot>

    @php
        $modal = new \App\Support\FormModal('movements');
        $pigOptions = $pigs->mapWithKeys(fn ($p) => [$p->id => $p->code.' ('.($p->pen?->name ?? '-').')'])->all();
        $penOptions = $pens->mapWithKeys(fn ($p) => [$p->id => $p->name.' ('.($p->branch?->name ?? '-').')'])->all();

        // Payload dirakit di @php lalu dipakai sebagai string: atribut class
        // komponen dievaluasi di scope terpisah yang tidak mewarisi variabel
        // view, jadi route()/$modal di sana undefined. `alpineData()` sudah
        // mengembalikan string `JSON.parse('...')` — jangan dipanggil `()`.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Pindahkan Ternak',
            'subtitle' => 'Kandang tujuan harus punya kapasitas cukup.',
            'action' => route('movements.store'),
            'values' => ['moved_at' => now()->toDateString()],
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <form method="GET" class="flex flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode ternak..." class="w-52" />
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>

            <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-arrows-right-left class="h-4 w-4" />
                Pindahkan
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Tanggal</th>
                        <th class="table-head-cell">Ternak</th>
                        <th class="table-head-cell">Dari</th>
                        <th class="table-head-cell">Ke</th>
                        <th class="table-head-cell">Alasan</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($movements as $m)
                        <tr class="table-row">
                            <td class="table-cell">{{ $m->moved_at->format('d M Y') }}</td>
                            <td class="table-cell font-medium">{{ $m->pig->code }}</td>
                            <td class="table-cell">{{ $m->fromPen?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $m->toPen?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $m->reason ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada perpindahan"
                                    description="Riwayat perpindahan antisensekan maternal akan muncul di sini.">
                                    <x-slot:action>
                                        <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-arrows-right-left class="h-4 w-4" />
                                            Pindahkan
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $movements->links() }}
    </div>

    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        title="Pindahkan Ternak"
        subtitle="Kandang tujuan harus punya kapasitas cukup.">

        <form method="POST" x-on:submit="onSubmit()" action="{{ route('movements.store') }}">
            @csrf
            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="pig_id" label="Ternak" type="select" required :options="$pigOptions" :span="2" />
                <x-form-field name="to_pen_id" label="Kandang Tujuan" type="select" required :options="$penOptions" />
                <x-form-field name="moved_at" label="Tanggal" type="date" required />
                <x-form-field name="reason" label="Alasan" required hint="Contoh: pisah setelah weaning" :span="2" />
                <x-form-field name="notes" label="Catatan" type="textarea" :span="2" />
            </div>

            <div class="mt-6 flex items-center gap-3 border-t border-neutral-100 pt-4">
                <x-primary-button type="submit" x-bind:disabled="saving">
                    <span x-show="!saving">Pindahkan</span>
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
