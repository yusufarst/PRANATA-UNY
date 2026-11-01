import { resolve } from 'node:path';
import { defineConfig } from 'vite-plus';

// Separate from vite.config.ts: the Laravel plugin refuses to start a dev server in CI.
export default defineConfig({
    resolve: {
        alias: { '@': resolve(import.meta.dirname, 'resources/js') },
    },
    test: {
        include: ['resources/js/**/*.test.{ts,tsx}'],
    },
});
