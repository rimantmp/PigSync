@props([
    'name',
    'open' => false,
    'errors' => [],
    'submitLabel' => 'Simpan',
])

{{--
    Modal form tambah/edit (Design.md §9.11).

    Form tetap POST biasa. Kalau validasi gagal, controller me-redirect dengan
    flash `form_modal` = $name; halaman dirender ulang, modal terbuka, `old()`
    mengisi ulang isian, dan pesan error tampil di dalam modal.
--}}
<div
    x-data="formModal()"
    x-init="$nextTick(() => { if ({{ alpineData($open) }}) { open = true; } })"
    x-on:open-form-modal.window="if ($event.detail?.modal === {{ alpineData($name) }}) onOpen($event.detail)"
    x-on:close-modal.window="if ($event.detail === {{ alpineData($name) }}) onClose()"
    x-on:keydown.escape.window="onClose()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    role="dialog"
    aria-modal="true"
    :aria-label="title">

    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-neutral-900/40"
         @click="onClose()" aria-hidden="true"></div>

    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
         class="relative mx-auto mb-6 w-full sm:max-w-2xl">

        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-md">

            <div class="flex items-start justify-between gap-4 border-b border-neutral-200 px-5 py-4">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-neutral-900" x-text="title"></h2>
                    <p class="mt-0.5 text-[13px] text-neutral-500" x-show="subtitle" x-text="subtitle"></p>
                </div>
                <button type="button" @click="onClose()"
                        class="-mr-1 -mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-700"
                        aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            @if (count($errors) > 0)
                <div class="px-5 pt-4">
                    <x-alert variant="error" title="Periksa kembali isian berikut">
                        <ul>
                            @foreach ($errors as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                </div>
            @endif

            <div class="flex-1 overflow-y-auto px-5 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
