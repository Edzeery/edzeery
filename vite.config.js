import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Legacy (untouched) — TailAdmin pages.
                "resources/css/app.css",
                "resources/js/app.js",
                // New design system — panel bundle (Tailwind + SCSS 7-1).
                "resources/css/app.scss",
                "resources/js/panel.js",
                // Storefront — lightweight bundle (no Alpine; Livewire provides it).
                "resources/js/storefront.js",
                // Storefront + landing builder (Bootstrap, scoped SCSS).
                "resources/css/storefront.scss",
                // Global loader — shared by every layout (panel, auth, marketing, storefront).
                "resources/js/edz-loader.js",
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
