import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'url';
import { resolve } from 'path';

const root = fileURLToPath(new URL('.', import.meta.url));
const appRoot = resolve(root, '../../../');

export default defineConfig(({ mode }) => {
    return {
        root,
        envDir: appRoot,
        plugins: [
            laravel({
                input: ['resources/css/theme.css', 'resources/js/theme.js'],
                buildDirectory: 'themes/tentapress/tailwind/build',
                hotFile: resolve(appRoot, 'public/themes/tentapress/tailwind/hot'),
            }),
            tailwindcss(),
        ],
        build: {
            outDir: resolve(root, '../../../public/themes/tentapress/tailwind/build'),
            emptyOutDir: true,
            manifest: 'manifest.json',
        },
    };
});
