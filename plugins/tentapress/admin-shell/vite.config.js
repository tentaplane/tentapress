import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [tailwindcss()],
    publicDir: false,
    build: {
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                admin: path.resolve('resources/js/admin.js'),
                'admin-styles': path.resolve('resources/css/admin.css'),
            },
            output: {
                assetFileNames: '[name]-[hash][extname]',
                entryFileNames: '[name]-[hash].js',
            },
        },
        outDir: path.resolve(path.dirname(fileURLToPath(import.meta.url)), 'build'),
    },
    server: {
        origin: 'http://localhost:5173',
    },
});
