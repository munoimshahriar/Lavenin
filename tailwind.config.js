import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // Keep in sync with :root tokens in resources/css/app.css
                cream: '#FAF7F2',
                teal: { DEFAULT: '#76A9AD', deep: '#4F8488' },
                slate: { DEFAULT: '#30363F' },
                coral: '#FF5722',
                mint: '#E3EFEF',
            },
            fontFamily: {
                display: ['Fredoka', 'Hind Siliguri', ...defaultTheme.fontFamily.sans],
                sans: ['Nunito', 'Hind Siliguri', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                card: '1.25rem',
            },
            boxShadow: {
                soft: '0 6px 24px -8px rgb(48 54 63 / 0.15)',
            },
        },
    },
    plugins: [],
};
