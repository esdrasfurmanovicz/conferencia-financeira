import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Schibsted Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Tinta: o verde-petróleo escuro da barra lateral e dos botões principais.
                ink: {
                    DEFAULT: '#12262B',
                    soft: '#1B3A41',
                    mute: '#5C7177',
                },
                paper: '#F1F4F1',
                rule: '#DCE2DD',
                // Conferido / pendente / alerta
                ok: { DEFAULT: '#1F8F5F', soft: '#E3F3EA', ink: '#126B43' },
                warn: { DEFAULT: '#B9791B', soft: '#FBF0DA', ink: '#8A5710' },
                bad: { DEFAULT: '#B73A30', soft: '#FBE7E4', ink: '#8E2A22' },
            },
            boxShadow: {
                lift: '0 1px 2px rgba(18,38,43,.06), 0 8px 24px -12px rgba(18,38,43,.18)',
            },
        },
    },

    plugins: [forms],
};
