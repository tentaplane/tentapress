import fs from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const resolveInputs = (entries) => entries.filter((entry) => fs.existsSync(entry));

export default defineConfig({
    plugins: [
        laravel({
            input: resolveInputs([
                // Admin
                'plugins/tentapress/admin-shell/resources/css/admin.css',
                'plugins/tentapress/admin-shell/resources/js/admin.js',

                // Optional plugin assets
                'plugins/tentapress/block-markdown-editor/resources/js/markdown-editor.js',

                // Theme fallback CSS
                'resources/css/fallback.css',
            ]),
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
