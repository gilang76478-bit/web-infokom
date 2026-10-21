/* Pengujian browser halaman FORMULIR PPDB SIAP CETAK (/ppdb/formulir).

   Menangkap regresi seperti: halaman tidak dapat diakses dari PPDB,
   kolom isian hilang, tombol cetak tidak memanggil dialog cetak, dan
   gaya @media print yang bocor menampilkan navigasi di hasil cetak. */
import { test, expect } from '@playwright/test';

test('halaman PPDB menautkan formulir cetak dan halaman formulir terbuka lengkap', async ({ page }) => {
    await page.goto('/ppdb');

    const tautan = page.locator('main a', { hasText: 'Cetak formulir pendaftaran' }).first();
    await expect(tautan).toBeVisible();
    await tautan.click();

    await expect(page).toHaveURL(/\/ppdb\/formulir$/);
    await expect(page.locator('h1')).toContainText('Formulir Pendaftaran');

    // Kop surat & bagian formulir lengkap untuk diisi tangan.
    await expect(page.locator('.ppdb-print-school')).toContainText('SMK INFOKOM');
    await expect(page.getByText('A. Data Calon Murid')).toBeVisible();
    await expect(page.getByText('B. Pilihan Program Keahlian')).toBeVisible();
    await expect(page.getByText('C. Data Orang Tua / Wali')).toBeVisible();
    await expect(page.getByText('D. Kelengkapan Dokumen')).toBeVisible();
    await expect(page.getByText('E. Pernyataan')).toBeVisible();

    // Kolom isian bergaris dan kotak centang tampil.
    await expect(page.locator('.ppdb-print-line').first()).toBeVisible();
    await expect(page.locator('.ppdb-print-box').first()).toBeVisible();
});

test('tombol cetak memanggil dialog cetak dan tersedia dua kali', async ({ page }) => {
    // Ganti window.print sebelum skrip halaman berjalan agar klik terekam.
    await page.addInitScript(() => {
        window.__printCalled = 0;
        window.print = () => {
            window.__printCalled += 1;
        };
    });

    await page.goto('/ppdb/formulir');

    const tombol = page.locator('[data-print-form]');
    await expect(tombol).toHaveCount(2);

    await tombol.first().click();
    expect(await page.evaluate(() => window.__printCalled)).toBe(1);
});

test('tombol unduh PDF mengunduh berkas PDF yang sah', async ({ page }) => {
    await page.goto('/ppdb/formulir');

    const unduhan = Promise.all([page.waitForEvent('download')]).then(([d]) => d);
    await page.locator('[data-download-pdf]').first().click();

    const download = await unduhan;
    expect(download.suggestedFilename()).toBe('formulir-ppdb-smk-infokom.pdf');

    // Baca aliran berkasnya: benar-benar PDF, bukan halaman HTML error.
    const stream = await download.createReadStream();
    const potongan = await new Promise((resolve, reject) => {
        stream.once('data', resolve);
        stream.once('error', reject);
    });
    expect(potongan.subarray(0, 5).toString()).toBe('%PDF-');
});

test('gaya cetak menyembunyikan navigasi dan footer', async ({ page }) => {
    await page.goto('/ppdb/formulir');
    await page.emulateMedia({ media: 'print' });

    // Pada media cetak, navigasi, footer, hero, dan tombol tidak ikut tercetak.
    await expect(page.locator('.nav')).toBeHidden();
    await expect(page.locator('.foot')).toBeHidden();
    await expect(page.locator('.detail-hero')).toBeHidden();
    await expect(page.locator('.ppdb-print-actions').first()).toBeHidden();

    // Lembar formulir tetap tampil.
    await expect(page.locator('.ppdb-print-sheet')).toBeVisible();
});
