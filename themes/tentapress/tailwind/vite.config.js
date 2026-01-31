import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'url';
import { resolve } from 'path';

const root = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
    root,
    plugins: [
        laravel({
            input: ['resources/css/theme.css', 'resources/js/theme.js'],
            hotFile: resolve(root, '../../../public/themes/tentapress/tailwind/hot'),
            buildDirectory: 'themes/tentapress/tailwind/build',
        }),
        tailwindcss(),
    ],
    build: {
        outDir: resolve(root, '../../../public/themes/tentapress/tailwind/build'),
        emptyOutDir: true,
        manifest: 'manifest.json',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
