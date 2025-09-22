import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(async ({ mode }) => {
    const plugins = [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ];

    // Wayfinder hanya untuk dev
    if (mode !== 'production') {
        const { wayfinder } = await import('@laravel/vite-plugin-wayfinder');
        plugins.push(wayfinder({ formVariants: true }));
    }

    return {
        plugins,
    };
});
