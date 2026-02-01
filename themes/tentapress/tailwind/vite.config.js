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
    const url = new URL(appUrl);
    const hmrHost = env.VITE_HMR_HOST || url.hostname;
    const hmrProtocol = env.VITE_HMR_PROTOCOL || url.protocol.replace(':', '');
    const hmrPort = env.VITE_HMR_PORT ? Number(env.VITE_HMR_PORT) : 5173;

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
            host: env.VITE_DEV_SERVER_HOST || 'localhost',
            origin: `${url.protocol}//${hmrHost}:${hmrPort}`,
            hmr: {
                host: hmrHost,
                protocol: hmrProtocol,
                port: hmrPort,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
