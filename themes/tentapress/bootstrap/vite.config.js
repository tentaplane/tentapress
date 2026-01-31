import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'url';
import { resolve } from 'path';

const root = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
    root,
    plugins: [
        laravel({
            input: ['resources/css/theme.css', 'resources/js/theme.js'],
            hotFile: resolve(root, '../../../public/themes/tentapress/bootstrap/hot'),
            buildDirectory: 'themes/tentapress/bootstrap/build',
        }),
    ],
    build: {
        outDir: resolve(root, '../../../public/themes/tentapress/bootstrap/build'),
        emptyOutDir: true,
        manifest: 'manifest.json',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
