import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { google } from "laravel-vite-plugin/fonts";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/site.css",
                "resources/css/admin.css",
                "resources/js/site.js",
                "resources/js/admin.js",
            ],
            refresh: true,
            // Font diunduh sekali saat build lalu disajikan dari /build
            // dengan hash (self-hosted) — halaman tidak lagi memuat
            // styles eksternal dari fonts.googleapis.com.
            fonts: [
                google("Bricolage Grotesque", {
                    weights: [600, 700, 800],
                    subsets: ["latin"],
                    display: "swap",
                }),
                google("Plus Jakarta Sans", {
                    weights: [400, 500, 600, 700, 800],
                    subsets: ["latin"],
                    display: "swap",
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
