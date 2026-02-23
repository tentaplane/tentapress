import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    plugins: [vue()],
    publicDir: false,
    build: {
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'builder-editor': path.resolve('resources/js/main.ts'),
                'builder-editor-styles': path.resolve('resources/css/builder-editor.css'),
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
