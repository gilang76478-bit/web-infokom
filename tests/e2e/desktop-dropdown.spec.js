/* Pengujian browser alur DROPDOWN NAVBAR desktop.

   Menangkap regresi seperti: label induk bukan tautan, submenu dekoratif
   (tautan mati), dropdown tidak tertutup, fokus keyboard hilang, dan anchor
   yang tertutup navbar sticky. */
import { test, expect } from '@playwright/test';

const caretAkademik = (page) => page.locator('a.nav-parent[href$="/akademik"] + .nav-caret');
const submenuAkademik = (page) => page.locator('#nav-sub-akademik');

test('caret membuka submenu dan klik di luar navigasi menutupnya', async ({ page }) => {
    await page.goto('/');

    await expect(submenuAkademik(page)).toBeHidden();
    await caretAkademik(page).click();
    await expect(submenuAkademik(page)).toBeVisible();
    await expect(caretAkademik(page)).toHaveAttribute('aria-expanded', 'true');

    await page.locator('main h1').click();
    await expect(submenuAkademik(page)).toBeHidden();
    await expect(caretAkademik(page)).toHaveAttribute('aria-expanded', 'false');
});

test('klik di dalam panel submenu tidak menutup dropdown (jebakan composedPath)', async ({ page }) => {
    await page.goto('/');
    await caretAkademik(page).click();

    // Klik pada padding panel — event.target berada di dalam dropdown,
    // jadi komposisi path asli harus dihormati (regresi fix composedPath).
    await submenuAkademik(page).click({ position: { x: 5, y: 5 } });

    await expect(submenuAkademik(page)).toBeVisible();
    await expect(caretAkademik(page)).toHaveAttribute('aria-expanded', 'true');
});

test('Escape menutup submenu dan mengembalikan fokus ke tombol caret', async ({ page }) => {
    await page.goto('/');
    await caretAkademik(page).click();
    await expect(caretAkademik(page)).toBeFocused();

    await page.keyboard.press('Escape');

    await expect(submenuAkademik(page)).toBeHidden();
    await expect(caretAkademik(page)).toHaveAttribute('aria-expanded', 'false');
    await expect(caretAkademik(page)).toBeFocused();
});

test('label induk (Profil & dropdown) membuka halaman bagian dari atas tanpa anchor', async ({ page }) => {
    await page.goto('/');

    // Profil adalah tautan langsung ke bagian teratas — tanpa potongan hash.
    await expect(page.locator('#menu > a', { hasText: /^Profil$/ })).toHaveAttribute('href', /\/profil$/);

    await page.locator('a.nav-parent[href$="/akademik"]').click();
    await page.waitForURL('**/akademik');

    expect(page.url()).not.toContain('#');
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0);
});

test('link anchor submenu berhenti di bawah navbar sticky, bukan tertutup', async ({ page }) => {
    await page.goto('/');
    await caretAkademik(page).click();
    await page.getByRole('link', { name: 'Program Keahlian', exact: true }).click();
    await page.waitForURL('**/akademik#program-keahlian');

    const { targetTop, navBottom } = await page.evaluate(() => ({
        targetTop: document.getElementById('program-keahlian').getBoundingClientRect().top,
        navBottom: document.querySelector('.nav').getBoundingClientRect().bottom,
    }));
    expect(targetTop).toBeGreaterThanOrEqual(navBottom - 1);
    await expect(page.locator('[data-nav-dropdown][data-open]')).toHaveCount(0);
});

test('klik anchor sesama halaman menutup dropdown tanpa reload halaman', async ({ page }) => {
    await page.goto('/akademik');
    await caretAkademik(page).click();

    // Penanda hanya bertahan bila halaman TIDAK dimuat ulang.
    await page.evaluate(() => {
        window.__noReloadMarker = 1;
    });

    await page.getByRole('link', { name: 'Program Keahlian', exact: true }).click();
    await expect(page.locator('[data-nav-dropdown][data-open]')).toHaveCount(0);

    expect(new URL(page.url()).hash).toBe('#program-keahlian');
    expect(await page.evaluate(() => window.__noReloadMarker)).toBe(1);
});

test('alur dropdown lengkap tidak memicu error JavaScript', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    await page.goto('/');
    await caretAkademik(page).click();
    await page.keyboard.press('Escape');
    await caretAkademik(page).click();
    await page.locator('main h1').click();
    await page.locator('a.nav-parent[href$="/kesiswaan"]').click();
    await page.waitForURL('**/kesiswaan');

    expect(errors).toEqual([]);
});
