{{-- Master index generik — tambah & edit lewat modal, bukan halaman terpisah. --}}
<x-app-layout>
    <x-slot name="title">{{ $label }}</x-slot>

    @php
        $modal = new \App\Support\FormModal($prefix);

        // Nilai form untuk mode edit — semua kolom form, bukan hanya yang
        // tampil di tabel, supaya field yang tidak ikut kolom ikut terisi.
        $rowValues = fn ($row) => collect($definitions)
            ->mapWithKeys(fn ($def, $name) => [$name => (string) ($row->{$name} ?? '')])
            ->all();

        // Mode create: semua isian kosong.
        $createValues = collect($definitions)
            ->mapWithKeys(fn ($def, $name) => [$name => ''])
            ->all();

        // Payload dihitung SEBELUM dipakai, lalu dipakai sebagai string.
        // Atribut class komponen dievaluasi di scope terpisah yang tidak
        // mewarisi variabel view, jadi $modal/$storeUrl undefined di sana —
        // termasuk di dalam closure yang dievaluasi di sana.
        $createPayload = alpineData([
            'modal' => $modal->name(),
            'mode' => 'create',
            'title' => 'Tambah '.$label,
            'subtitle' => 'Lengkapi data '.strtolower($label).'.',
            'action' => $storeUrl,
            'method' => 'POST',
            'values' => $createValues,
        ]);

        $editPayload = fn ($row) => alpineData([
            'modal' => $modal->name(),
            'mode' => 'edit',
            'title' => 'Edit '.$label,
            'subtitle' => 'Perbarui data '.strtolower($label).' ini.',
            'action' => route($prefix.'.update', $row),
            'method' => 'POST',
            'values' => $rowValues($row),
        ]);
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <form method="GET" class="flex flex-1 gap-2">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Cari kode / nama..." class="w-full max-w-xs" />
                <x-secondary-button type="submit">Cari</x-secondary-button>
            </form>

            <x-primary-button type="button"
                x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                <x-heroicon-o-plus class="h-4 w-4" />
                Tambah {{ $label }}
            </x-primary-button>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell w-12">#</th>
                        @foreach ($columns as $col)
                            @php $def = $definitions[$col] ?? ['label' => $col]; @endphp
                            <th class="table-head-cell">{{ $def['label'] }}</th>
                        @endforeach
                        <th class="table-head-cell text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse ($rows as $row)
                        <tr class="table-row">
                            <td class="table-cell text-neutral-400">{{ $row->id }}</td>
                            @foreach ($columns as $col)
                                <td class="table-cell">
                                    @if ($col === 'branch_id')
                                        {{ $row->branch?->name ?? '-' }}
                                    @elseif ($col === 'area_id')
                                        {{ $row->area?->name ?? '-' }}
                                    @elseif ($col === 'unit_id')
                                        {{ $row->unit?->name ?? '-' }}
                                    @elseif (isset($definitions[$col]) && $definitions[$col]['type'] === 'select' && is_numeric($row->{$col}))
                                        {{ $row->{$col} == 1 ? 'Ya' : 'Tidak' }}
                                    @else
                                        {{ $row->{$col} ?? '-' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="table-cell text-right whitespace-nowrap">
                                <button type="button"
                                        class="text-primary-600 hover:text-primary-800"
                                        x-on:click="$dispatch('open-form-modal', {{ $editPayload($row) }})">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route($prefix.'.destroy', $row) }}" class="inline ms-3"
                                      onsubmit="return confirm('Hapus {{ $label }} ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-error-600 hover:text-error-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 2 }}" class="px-4 py-10">
                                <x-empty-state
                                    title="Belum ada {{ strtolower($label) }}"
                                    :description="'Data '.strtolower($label).' yang Anda tambahkan akan muncul di sini.'">
                                    <x-slot:action>
                                        <x-primary-button type="button"
                                            x-on:click="$dispatch('open-form-modal', {{ $createPayload }})">
                                            <x-heroicon-o-plus class="h-4 w-4" />
                                            Tambah {{ $label }}
                                        </x-primary-button>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>

    <x-form-modal
        :name="$modal->name()"
        :open="$modal->isOpen()"
        :errors="$modal->errors()"
        :title="'Tambah '.$label"
        :subtitle="'Lengkapi data '.strtolower($label).'.'">

        <form method="POST" x-ref="form" x-on:submit="onSubmit()" x-bind:action="action">
            @csrf

            <input type="hidden" name="form_modal" value="{{ $modal->name() }}">
            <input type="hidden" name="form_back" value="{{ url()->current() }}">

            {{-- x-if, bukan x-show: field harus benar-benar tidak ada saat
                 create, kalau tidak Laravel akan menerima method "PUT" pada
                 route store. --}}
            <template x-if="mode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($definitions as $name => $def)
                    @php $type = $def['type'] ?? 'text'; @endphp

                    <x-form-field
                        :name="$name"
                        :label="$def['label'] ?? $name"
                        :type="$type"
                        :options="$fieldOptions[$name] ?? null"
                        :required="($def['required'] ?? false)"
                        :span="$type === 'textarea' ? 2 : 1" />
                @endforeach
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
