import { defineConfig } from 'vite';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    publicDir: false,
    build: {
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'markdown-editor': path.resolve('resources/js/markdown-editor.js'),
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
