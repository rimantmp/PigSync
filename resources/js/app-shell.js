/**
 * State shell aplikasi: drawer mobile + collapse sidebar desktop.
 *
 * Desktop menyimpan pilihan collapse agar tidak reset tiap pindah halaman.
 */
const COLLAPSE_KEY = 'sk.sidebar.collapsed';

/**
 * Baca pilihan collapse dari localStorage, aman dipanggil di property
 * initializer (tanpa bergantung pada `this`).
 */
function loadCollapsed() {
    try {
        return localStorage.getItem(COLLAPSE_KEY) === '1';
    } catch {
        return false;
    }
}

export function appShell() {
    return {
        sidebarOpen: false,
        // `this` belum terikat saat property initializer dievaluasi Alpine,
        // jadi baca localStorage lewat fungsi biasa, bukan metode.
        sidebarCollapsed: loadCollapsed(),

        init() {
            // Drawer harus bisa ditutup dengan Escape dan mengunci scroll body.
            this.$watch('sidebarOpen', (open) => {
                document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 768);
            });
        },

        toggleCollapsed() {
            this.sidebarCollapsed = !this.sidebarCollapsed;

            try {
                localStorage.setItem(COLLAPSE_KEY, this.sidebarCollapsed ? '1' : '0');
            } catch {
                // Private mode: abaikan, collapse hanya berlaku sesi ini.
            }
        },
    };
}
