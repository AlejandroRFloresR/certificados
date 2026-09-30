import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/inertia.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    base: process.env.ASSET_URL ? process.env.ASSET_URL + '/build/' : '/build/',
    resolve: { alias: { '@': path.resolve(__dirname, 'resources/js') } },
     server: {
        host: '0.0.0.0',                              // escucha en todas las interfaces de red
        hmr: { host: '172.22.116.165' },              // la dirección que se escribe en public/hot
        cors: { origin: 'http://172.22.116.165' },    // permite que tu app cargue los scripts
    },
});
