import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const projectRoot = path.resolve(__dirname, '../../..');
const resolveInputs = (entries) =>
    entries.map((entry) => path.resolve(projectRoot, entry)).filter((entry) => fs.existsSync(entry));
const adminInputs = resolveInputs([
    // Admin
    'plugins/tentapress/admin-shell/resources/css/admin.css',
    'plugins/tentapress/admin-shell/resources/js/admin.js',
]);

export default defineConfig({
    root: projectRoot,
    plugins: [
        laravel({
            input: adminInputs,
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        fs: {
            allow: [projectRoot],
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
