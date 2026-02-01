import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'url';
import { resolve } from 'path';

const root = fileURLToPath(new URL('.', import.meta.url));
const appRoot = resolve(root, '../../../');

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, appRoot, '');
    const appUrl = env.APP_URL || 'http://localhost';

    return {
        root,
        envDir: appRoot,
        plugins: [
            laravel({
                input: ['resources/css/theme.css', 'resources/js/theme.js'],
                hotFile: resolve(root, '../../../public/themes/tentapress/tailwind/hot'),
                buildDirectory: 'themes/tentapress/tailwind/build',
                refresh: [
                    'views/**/*.blade.php',
                    '../../plugins/**/resources/views/**/*.blade.php',
                    '../../packages/**/resources/views/**/*.blade.php',
                ],
            }),
            tailwindcss(),
        ],
        build: {
            outDir: resolve(root, '../../../public/themes/tentapress/tailwind/build'),
            emptyOutDir: true,
            manifest: 'manifest.json',
        },
        server: {
            origin: appUrl,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
