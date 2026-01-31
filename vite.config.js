import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Admin
                'plugins/tentapress/admin-shell/resources/css/admin.css',
                'plugins/tentapress/admin-shell/resources/js/admin.js',

                // Theme fallback CSS
                'resources/css/fallback.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
