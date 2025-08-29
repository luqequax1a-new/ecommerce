import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/admin/seo-preview.js',
                'resources/js/admin/product-quick-edit.js',
                'resources/js/admin/product-form-toggle.js',
                'resources/js/admin/product-clone.js',
                'resources/js/admin/product-image-manager.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});