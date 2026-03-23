import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        // Allow tunneling tools (localtunnel) to connect over IPv4.
        host: '0.0.0.0',
        // Permit public tunnel hostnames used in development.
        allowedHosts: ['localhost', '.trycloudflare.com'],
        strictPort: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
