/**
 * State modal form tambah/edit.
 *
 * Satu modal dipakai untuk mode create dan edit. Baris tabel mengirim data
 * lewat event `open-form-modal`; nilainya ditulis langsung ke input di dalam
 * form, karena `old()` hanya terisi setelah submit gagal.
 *
 * Event:
 *   $dispatch('open-form-modal', { modal, mode, title, subtitle, action, method, values })
 *   $dispatch('close-modal', namaModal)
 */
export function formModal() {
    return {
        mode: 'create',
        open: false,
        saving: false,
        action: '',
        title: '',
        subtitle: '',
        values: {},

        /**
         * Nama modal ini, diambil dari form hidden `form_modal` supaya tidak
         * perlu diteruskan sebagai atribut terpisah.
         */
        name: '',

        init() {
            this.name = this.$el.querySelector('input[name="form_modal"]')?.value ?? '';
        },

        onOpen(payload) {
            if (payload?.modal && this.name && payload.modal !== this.name) {
                return;
            }

            this.mode = payload?.mode ?? 'create';
            this.open = true;
            this.saving = false;
            this.action = payload?.action ?? '';
            this.title = payload?.title ?? '';
            this.subtitle = payload?.subtitle ?? '';
            this.values = payload?.values ?? {};

            this.$nextTick(() => this.fill());
        },

        onClose() {
            this.open = false;
        },

        /**
         * Tulis nilai ke input form.
         *
         * Field yang tidak ada di `values` dibiarkan apa adanya supaya
         * `old()` dari submit yang gagal tidak tertimpa.
         *
         * Penting: form bisa punya blok create DAN edit dengan nama field yang
         * sama (mis. `sex` ada di dua-duanya), dan keduanya ada di DOM walau
         * salah satunya disembunyikan. Kalau keduanya diisi, request akan
         * mengirim `sex` dua kali dan_rules bisa bentrok. Jadi hanya field di
         * blok yang sedang terlihat yang ditulis.
         */
        fill() {
            this.$el.querySelectorAll('input[name], select[name], textarea[name]').forEach((el) => {
                if (el.type === 'hidden' || el.type === 'submit') return;
                if (el.name === '_method' || el.name === 'form_modal' || el.name === 'form_back') return;
                if (this.isHidden(el)) return;

                const value = this.values[el.name];
                if (value !== undefined && value !== null) {
                    el.value = String(value);
                }
            });
        },

        /**
         * Apakah field disembunyikan oleh `x-show` pada bloknya?
         */
        isHidden(el) {
            const block = el.closest('[x-show]');
            if (!block) return false;

            // x-show menyetel style.display = 'none'; Alpine juga menambah
            // atribut hidden pada elemen yang disembunyikan.
            if (block.hasAttribute('hidden')) return true;

            return block.style?.display === 'none';
        },

        /**
         * Cegah submit ganda: tombol nonaktif selama request berjalan.
         */
        onSubmit() {
            if (this.saving) {
                return false;
            }

            this.saving = true;
            return true;
        },
    };
}
