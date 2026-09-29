<x-app-layout>
    <x-slot name="title">Data Babi</x-slot>

    @php
        $modal = new \App\Support\FormModal('pigs');

        $breedOptions = $breeds->pluck('name', 'id')->all();
        $phaseOptions = $phases->pluck('name', 'id')->all();
        $penOptions = $pens->mapWithKeys(fn ($p) => [$p->id => $p->name.' ('.($p->branch?->name ?? '-').')'])->all();

        // Payload dirakit di @php, bukan inline di atribut class komponen:
        // atribut komponen dievaluasi di scope terpisah yang tidak mewarisi
        // variabel view, jadi $modal/route() di sana akan undefined.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Registrasi Ternak',
            'subtitle' => 'Isi identitas dasar ternak. Kandang wajib dipilih.',
            'action' => route('pigs.store'),
            'values' => [
                'birth_date' => now()->toDateString(),
                'origin_type' => 'internal',
                'sex' => 'betina',
            ],
        ]);

        $editPayload = fn ($pig) => alpineData([
            'modal' => $modal->name(),
            'mode' => 'edit',
            'title' => 'Edit '.$pig->code,
            'subtitle' => 'Perbarui data dasar ternak.',
            'action' => route('pigs.update', $pig),
            'values' => [
                'sex' => $pig->sex,
                'breed_id' => (string) $pig->breed_id,
                'phase_id' => (string) $pig->phase_id,
                'tag_id' => (string) $pig->tag_id,
                'rfid' => (string) $pig->rfid,
                'notes' => (string) $pig->notes,
            ],
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" class="flex flex-wrap flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode / ear tag..." class="w-56" />
                <select name="status" class="rounded-lg border-neutral-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="pen_id" class="rounded-lg border-neutral-300 text-sm">
                    <option value="">Semua kandang</option>
                    @foreach ($pens as $p)
                        <option value="{{ $p->id }}" @selected(request('pen_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>

            <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-plus class="h-4 w-4" />
                Registrasi Ternak
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Kode</th>
                        <th class="table-head-cell">Tag</th>
                        <th class="table-head-cell">Kelamin</th>
                        <th class="table-head-cell">Ras</th>
                        <th class="table-head-cell">Kandang</th>
                        <th class="table-head-cell">Cabang</th>
                        <th class="table-head-cell">Status</th>
                        <th class="table-head-cell text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($pigs as $pig)
                        <tr class="table-row">
                            <td class="table-cell font-medium">
                                <a href="{{ route('pigs.show', $pig) }}" class="text-neutral-900 hover:underline">{{ $pig->code }}</a>
                            </td>
                            <td class="table-cell">{{ $pig->tag_id ?? '—' }}</td>
                            <td class="table-cell">{{ $pig->sex }}</td>
                            <td class="table-cell">{{ $pig->breed?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $pig->pen?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $pig->pen?->branch?->name ?? '—' }}</td>
                            <td class="table-cell">
                                @php
                                    $statusVariant = match ($pig->status) {
                                        'aktif' => 'success',
                                        'sakit', 'karantina' => 'warning',
                                        'mati' => 'error',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-badge variant="{{ $statusVariant }}" dot>{{ $pig->status }}</x-badge>
                            </td>
                            <td class="table-cell text-right whitespace-nowrap">
                                <button type="button" class="text-primary-600 hover:text-primary-800"
                                        x-on:click="$dispatch('open-form-modal', {{ $editPayload($pig) }})">
                                    Edit
                                </button>
                                <a href="{{ route('pigs.show', $pig) }}" class="ms-3 text-neutral-600 hover:text-neutral-900">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada ternak"
                                    description="Ternak yang didaftarkan akan muncul di sini.">
                                    <x-slot:action>
                                        <x-primary-button type="button" x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-plus class="h-4 w-4" />
                                            Registrasi Ternak
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pigs->links() }}
    </div>

    {{-- Modal registrasi & edit --}}
    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        title="Registrasi Ternak"
        subtitle="Isi identitas dasar ternak.">

        <form method="POST" x-on:submit="onSubmit()" x-bind:action="action">
            @csrf

            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            <template x-if="mode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Field registrasi; disembunyikan saat edit --}}
            <div x-show="mode === 'create'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="sex" label="Jenis Kelamin" type="select" required
                              :options="['jantan' => 'Jantan', 'betina' => 'Betina']" />

                <x-form-field name="origin_type" label="Asal" type="select" required
                              :options="['internal' => 'Internal (lahir di farm)', 'eksternal' => 'Eksternal (beli)']" />

                <x-form-field name="birth_date" label="Tanggal Lahir" type="date" required />

                <x-form-field name="pen_id" label="Kandang Penempatan" type="select" required :options="$penOptions" />

                <x-form-field name="breed_id" label="Ras" type="select" :options="$breedOptions" />

                <x-form-field name="initial_weight" label="Berat Awal (kg)" type="number" hint="Contoh: 25" />
            </div>

            {{-- Field edit --}}
            <div x-show="mode === 'edit'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form-field name="sex" label="Jenis Kelamin" type="select" required
                              :options="['jantan' => 'Jantan', 'betina' => 'Betina']" />

                <x-form-field name="breed_id" label="Ras" type="select" :options="$breedOptions" />

                <x-form-field name="phase_id" label="Fase" type="select" :options="$phaseOptions" />

                <x-form-field name="tag_id" label="Ear Tag" />

                <x-form-field name="rfid" label="RFID" />

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
