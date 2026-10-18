/* Pengujian browser alur MENU MOBILE.

   Test pertama adalah jaring pengaman regresi utama: bug lama membuat menu
   langsung tertutup kembali tepat pada klik burger yang sama (event.target
   terlepas dari DOM setelah ikon diganti), sehingga menu praktis tidak
   pernah bisa dibuka di perangkat sentuh. */
import { test, expect } from '@playwright/test';

const openMenu = async (page) => {
    await page.locator('#burger').click();
    await expect(page.locator('#menu')).toHaveClass(/open/);
};

test('REGRESI: burger membuka menu mobile dan menu TETAP terbuka', async ({ page }) => {
    await page.goto('/');

    const menu = page.locator('#menu');
    const burger = page.locator('#burger');
    await expect(menu).toBeHidden();

    await burger.click();

    await expect(menu).toBeVisible();
    await expect(menu).toHaveClass(/open/);
    await expect(burger).toHaveAttribute('aria-expanded', 'true');

    // Bug lama menutup menu tepat setelah membukanya (sinkron pada klik yang
    // sama); jeda kecil juga menangkap penutupan yang terlambat.
    await page.waitForTimeout(300);
    await expect(menu).toHaveClass(/open/);
    await expect(burger).toHaveAttribute('aria-expanded', 'true');
});

test('submenu terbuka inline di dalam panel menu dan menu tetap terbuka', async ({ page }) => {
    await page.goto('/');
    await openMenu(page);

    const caret = page.locator('a.nav-parent[href$="/akademik"] + .nav-caret');
    await caret.click();

    const submenu = page.locator('#nav-sub-akademik');
    await expect(submenu).toBeVisible();
    await expect(caret).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#menu')).toHaveClass(/open/);

    // Submenu disajikan sebagai blok selebar panel, bukan dropdown melayang.
    const box = await submenu.boundingBox();
    expect(box.width).toBeGreaterThan(250);
});

test('ketuk item submenu menavigasi sambil menutup menu dan dropdown', async ({ page }) => {
    await page.goto('/');
    await openMenu(page);
    await page.locator('a.nav-parent[href$="/akademik"] + .nav-caret').click();
    await page.getByRole('link', { name: 'Program Keahlian', exact: true }).click();

    await page.waitForURL('**/akademik#program-keahlian');
    await expect(page.locator('#menu')).not.toHaveClass(/open/);
    await expect(page.locator('[data-nav-dropdown][data-open]')).toHaveCount(0);
    await expect(page.locator('#burger')).toHaveAttribute('aria-expanded', 'false');

    // Anchor berhenti di bawah header, bukan tertutup navbar.
    const { targetTop, navBottom } = await page.evaluate(() => ({
        targetTop: document.getElementById('program-keahlian').getBoundingClientRect().top,
        navBottom: document.querySelector('.nav').getBoundingClientRect().bottom,
    }));
    expect(targetTop).toBeGreaterThanOrEqual(navBottom - 1);
});

test('menu mobile: tanpa overflow horizontal dan target sentuh memadai', async ({ page }) => {
    await page.goto('/');
    await openMenu(page);

    const sizes = await page.evaluate(() => {
        const caret = document
            .querySelector('a.nav-parent[href$="/akademik"] + .nav-caret')
            .getBoundingClientRect();
        const link = document.querySelector('#menu > a').getBoundingClientRect();
        return {
            caretW: caret.width,
            caretH: caret.height,
            linkH: link.height,
            hasHorizontalOverflow: document.documentElement.scrollWidth > window.innerWidth,
        };
    });

    expect(sizes.hasHorizontalOverflow).toBe(false);
    expect(sizes.caretW).toBeGreaterThanOrEqual(44);
    expect(sizes.caretH).toBeGreaterThanOrEqual(44);
    expect(sizes.linkH).toBeGreaterThanOrEqual(44);
});

test('Escape menutup menu mobile dan mengembalikan fokus ke burger', async ({ page }) => {
    await page.goto('/');
    const burger = page.locator('#burger');

    await burger.click();
    await expect(page.locator('#menu')).toHaveClass(/open/);

    await page.keyboard.press('Escape');

    await expect(page.locator('#menu')).not.toHaveClass(/open/);
    await expect(burger).toHaveAttribute('aria-expanded', 'false');
    await expect(burger).toBeFocused();
});
