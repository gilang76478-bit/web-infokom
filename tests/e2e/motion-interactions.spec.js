/* Pengujian browser animasi interaksi halaman publik:
   - Lightbox galeri: klik foto → tampil penuh, panah/kunci keyboard
     menggeser ke foto berikutnya/sebelumnya, Escape menutup.
   - Carousel cerita alumni: tombol panah menggeser rel snap.
   - Tirai halaman: klik tautan internal memasang animasi tirai. */
import { test, expect } from '@playwright/test';

test('lightbox galeri terbuka dan bergeser lewat tombol serta keyboard', async ({ page }) => {
    await page.goto('/galeri');

    const items = page.locator('[data-lightbox-item]');
    await expect(items.first()).toBeVisible();
    const total = await items.count();

    // Klik foto pertama: lightbox tumbuh dari tengah dengan penghitung.
    await items.first().click();
    await expect(page.locator('.lightbox')).toBeVisible();
    await expect(page.locator('.lightbox-count')).toHaveText(`1 / ${total}`);

    // Tombol panah kanan: geser ke foto berikutnya.
    await page.locator('[data-lb-next]').click();
    await expect(page.locator('.lightbox-count')).toHaveText(`2 / ${total}`);

    // Keyboard ArrowLeft: geser kembali ke foto pertama.
    await page.keyboard.press('ArrowLeft');
    await expect(page.locator('.lightbox-count')).toHaveText(`1 / ${total}`);

    // Escape menutup lightbox dan mengunci guliran dilepas.
    await page.keyboard.press('Escape');
    await expect(page.locator('.lightbox')).toBeHidden();
    await expect
        .poll(() => page.evaluate(() => document.documentElement.classList.contains('lightbox-open')))
        .toBe(false);
});

test('carousel cerita alumni menggeser kartu lewat tombol panah', async ({ page }) => {
    // Lebih sempit dari layar desktop penuh agar rel benar-benar
    // meluap dan tombol panah kanan aktif.
    await page.setViewportSize({ width: 900, height: 900 });
    await page.goto('/alumni');

    const viewport = page.locator('[data-carousel-viewport]');
    await expect(viewport).toBeVisible();

    const next = page.locator('[data-carousel-next]');
    await expect(next).toBeEnabled();

    await next.click();
    await expect
        .poll(() => page.evaluate(() => document.querySelector('[data-carousel-viewport]')?.scrollLeft ?? 0))
        .toBeGreaterThan(0);

    // Setelah bergeser, tombol panah kiri ikut aktif (bukan di ujung rel).
    await expect(page.locator('[data-carousel-prev]')).toBeEnabled();
});

test('klik tautan internal memasang tirai halaman', async ({ page }) => {
    await page.goto('/');

    // Tautan probe memaksa navigasi dicegah SETELAH handler situs
    // berjalan, sehingga pemasangan tirai dapat diperiksa pada dokumen
    // yang sama tanpa menunggu halaman berikutnya dimuat.
    const curtainArmed = await page.evaluate(() => {
        document.addEventListener('click', (event) => {
            const anchor = event.target;
            if (anchor instanceof HTMLAnchorElement && anchor.dataset.curtainProbe) {
                event.preventDefault();
            }
        });

        const probe = document.createElement('a');
        probe.href = '/bkk';
        probe.dataset.curtainProbe = '1';
        document.body.append(probe);
        probe.click();
        probe.remove();

        return document.querySelector('.page-curtain')?.classList.contains('is-active') ?? false;
    });

    expect(curtainArmed).toBe(true);
});
