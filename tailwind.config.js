import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/blaravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/blade-ui-kit/blade-heroicons/resources/svg/*.svg',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            // Token warna mengikuti docs/Design.md §3. Nilai hex di sini
            // TIDAK sama dengan palet bawaan Tailwind (mis. orange-500
            // Tailwind #FF7A00, spec #F97316), jadi primary/success/warning/
            // error/info didefinisikan eksplisit, bukan overriding nama bawaan.
            colors: {
                primary: {
                    50: '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7AA',
                    300: '#FDBA74',
                    400: '#FB923C',
                    500: '#F97316',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
                },
                success: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    500: '#16A34A',
                    600: '#15803D',
                    700: '#166534',
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    500: '#D97706',
                    600: '#B45309',
                    700: '#92400E',
                },
                error: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    500: '#DC2626',
                    600: '#B91C1C',
                    700: '#991B1B',
                },
                info: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    500: '#2563EB',
                    600: '#1D4ED8',
                    700: '#1E40AF',
                },
            },

            // §4.1 — Inter, dengan fallback system yang crash.
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
            },

            // §2.4. Skala bawaan Tailwind sudah cocok dengan spec
            // (lg=8px, xl=12px, 2xl=16px), jadi hanya `sm` dan `pill`
            // yang ditambah — DEFAULT sengaja tidak diubah agar halaman
            // yang belum dimigrasi tidak ikut bergeserradius-nya.
            borderRadius: {
                sm: '6px',
                pill: '9999px',
            },

            // §6.5 — shadow minimal, hanya untuk elevation yang jelas.
            boxShadow: {
                sm: '0 1px 2px rgba(0, 0, 0, 0.05)',
                md: '0 4px 12px rgba(0, 0, 0, 0.08)',
            },

            // §6.2 — sidebar 248px expanded / 72px collapsed.
            spacing: {
                sidebar: '248px',
                'sidebar-collapsed': '72px',
            },

            // §17.16 — transisi singkat.
            transitionDuration: {
                DEFAULT: '150ms',
            },
        },
    },

    plugins: [forms],
};
