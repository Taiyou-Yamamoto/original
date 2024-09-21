import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        manifest: true,
        outDir: 'public/build',
    },
    server: {
        https: true,
    },
    plugins: [
        laravel({
            input: [
                // 'resources/sass/app.scss',
                'resources/css/slider.css', 
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/home.css',

            ],
            refresh: true,
        }),
    ],
});