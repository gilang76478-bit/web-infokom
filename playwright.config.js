/* Konfigurasi Playwright untuk pengujian browser alur navigasi publik.

   Dua project berjalan terhadap server yang sama:
   - desktop (1440×900): perilaku dropdown navbar.
   - mobile  (390×844, sentuh): perilaku menu mobile & burger.

   webServer memakai server yang sudah hidup (reuseExistingServer) atau
   menyalakan `php artisan serve` sendiri bila port belum tersedia. */
import { defineConfig, devices } from '@playwright/test';

const PORT = Number(process.env.E2E_PORT || 8237);
const baseURL = `http://127.0.0.1:${PORT}`;

export default defineConfig({
    testDir: 'tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: [['list']],
    timeout: 30_000,
    use: {
        baseURL,
        locale: 'id-ID',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'desktop',
            testMatch: /desktop-dropdown\.spec\.js/,
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 900 },
            },
        },
        {
            name: 'mobile',
            testMatch: /mobile-menu\.spec\.js/,
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 390, height: 844 },
                isMobile: true,
                hasTouch: true,
            },
        },
    ],
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${PORT}`,
        url: baseURL,
        reuseExistingServer: true,
        timeout: 30_000,
    },
});
