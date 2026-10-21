/* Pengujian browser pita jangkar profil (scrollspy).

   Menangkap regresi seperti: bagian aktif tidak ikut berpindah saat
   menggulir, pita tidak menempel di bawah navigasi utama, dan target
   jangkar tertutup pita lengket. */
import { test, expect } from '@playwright/test';

const pill = (page, hash) => page.locator(`.profile-subnav a[href="${hash}"]`);

test('pita jangkar menyorot bagian yang sedang dibaca saat menggulir', async ({ page }) => {
    await page.goto('/profil');

    // Pada bagian teratas, sambutan (bagian pertama) sudah disorot.
    await expect(pill(page, '#sambutan')).toHaveAttribute('aria-current', 'location');
    await expect(pill(page, '#sambutan')).toHaveClass(/is-active/);

    // Menggulir ke Fasilitas: sorotan berpindah ke pil yang sesuai.
    await page.evaluate(() => document.getElementById('fasilitas').scrollIntoView());
    await expect(pill(page, '#fasilitas')).toHaveAttribute('aria-current', 'location');
    await expect(pill(page, '#fasilitas')).toHaveClass(/is-active/);
    await expect(pill(page, '#sambutan')).not.toHaveAttribute('aria-current', 'location');
});

test('pita jangkar menempel di bawah navigasi utama dan target tidak tertutup', async ({ page }) => {
    await page.goto('/profil');

    // Klik jangkar: target harus mendarat di bawah pita utama + pita jangkar.
    await pill(page, '#keunggulan').click();
    await expect(page).toHaveURL(/#keunggulan$/);

    await expect.poll(async () => page.evaluate(() => {
        const nav = document.querySelector('.nav').getBoundingClientRect();
        const subnav = document.querySelector('.profile-subnav').getBoundingClientRect();
        const target = document.getElementById('keunggulan').getBoundingClientRect();

        return {
            stuck: Math.abs(subnav.top - nav.bottom) <= 2,
            cleared: target.top >= subnav.bottom - 1,
        };
    })).toEqual({ stuck: true, cleared: true });

    // Pita tetap menempel saat halaman digulir jauh ke bawah.
    await page.evaluate(() => window.scrollBy(0, 600));
    await expect.poll(async () => page.evaluate(() => {
        const nav = document.querySelector('.nav').getBoundingClientRect();
        const subnav = document.querySelector('.profile-subnav').getBoundingClientRect();

        return Math.abs(subnav.top - nav.bottom) <= 2;
    })).toBe(true);
});
