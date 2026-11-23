import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                bg: 'var(--bg)',
                surface: 'var(--surface)',
                text: 'var(--text)',
                'text-secondary': 'var(--text-2)',
                border: 'var(--border)',
                ink: 'var(--ink)',
                accent: 'var(--accent)',
                'accent-dark': 'var(--accent-dark)',
                'on-accent': 'var(--on-accent)',
                'warning-text': 'var(--warning-text)',
                'warning-bg': 'var(--warning-bg)',
                danger: 'var(--danger)',
                'admin-sidebar': 'var(--admin-sidebar)',
            },
            fontFamily: {
                heading: ['Caveat', 'cursive'],
                ui: ['Patrick Hand', 'cursive', 'sans-serif'],
                body: ['DM Sans', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
                sans: ['DM Sans', 'sans-serif'],
            },
            boxShadow: {
                sketch: '3px 3px 0 var(--ink)',
                'sketch-sm': '2px 2px 0 var(--ink)',
                'sketch-lg': '4px 4px 0 var(--ink)',
            },
            borderWidth: {
                sketch: '2px',
                'sketch-thick': '3px',
            },
            maxWidth: {
                container: '1200px',
                reading: '740px',
                toc: '220px',
            },
        },
    },

    plugins: [forms],
};
