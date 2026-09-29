<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * State form modal untuk tambah & edit.
 *
 * Form tetap POST biasa (bukan fetch), sesuai keputusan: kalau validasi gagal,
 * controller me-redirect balik sambil menandai modal mana yang harus dibuka dan
 * field mana yang bermasalah. Halaman dirender ulang, modal terbuka, `old()`
 * mengisi ulang isian, pesan error tampil di dalam modal.
 *
 * Penentuan "error milik form ini" TIDAK ditebak dari isi pesan (pesan default
 * Laravel tidak menyebut nama field). Controller yang menuliskannya secara
 * eksplisit lewat flash `form_keys`, jadi tidak ada tebakan.
 */
final class FormModal
{
    public function __construct(
        private readonly string $prefix,
    ) {}

    /**
     * Nama event Alpine untuk modal ini.
     */
    public function name(): string
    {
        return 'form-'.$this->prefix;
    }

    /**
     * Modal harus terbuka di render ini?
     *
     * Flash disimpan memakai nama modal ("form-units"), bukan prefix polos.
     */
    public function isOpen(): bool
    {
        return session('form_modal') === $this->name();
    }

    /**
     * Pesan error milik form ini saja, supaya modal edit tidak ikut menampilkan
     * error milik form lain di halaman yang sama.
     *
     * @return array<int, string>
     */
    public function errors(): array
    {
        if (! $this->isOpen()) {
            return [];
        }

        $bag = session('errors');
        $keys = session('form_keys', []);

        if ($bag === null || ! is_array($keys) || $keys === []) {
            return [];
        }

        $messages = [];

        foreach ($keys as $key) {
            foreach ((array) $bag->get($key) as $message) {
                $messages[] = $message;
            }
        }

        return array_values(array_unique($messages));
    }

    /**
     * URL form: update kalau $row sudah ada, selain itu store.
     */
    public function action(string $storeUrl, ?Model $row = null): string
    {
        if ($row !== null && $row->exists) {
            return route($this->prefix.'.update', $row);
        }

        return $storeUrl;
    }
}
