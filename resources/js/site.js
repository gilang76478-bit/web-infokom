/* Skrip global halaman publik: menu mobile, dropdown, dan
   utilitas kecil yang dipakai beberapa halaman. */
const menu = document.querySelector("#menu");
const burger = document.querySelector("#burger");
const nav = document.querySelector(".nav");
const mobileNavigation = window.matchMedia("(max-width: 1279px)");
const menuIcon =
    '<svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>';
const closeIcon =
    '<svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>';

if (burger) burger.innerHTML = menuIcon;

function closeMenu({ returnFocus = false } = {}) {
    menu?.classList.remove("open");
    burger?.setAttribute("aria-expanded", "false");
    burger?.setAttribute("aria-label", "Buka menu");
    if (burger) burger.innerHTML = menuIcon;
    if (returnFocus) burger?.focus();
}

burger?.addEventListener("click", () => {
    const open = menu?.classList.toggle("open") ?? false;
    burger.setAttribute("aria-expanded", String(open));
    burger.setAttribute("aria-label", open ? "Tutup menu" : "Buka menu");
    burger.innerHTML = open ? closeIcon : menuIcon;
});

/* Dropdown navigasi: label induk adalah tautan biasa (langsung ke bagian
   teratas halaman), tombol panah hanya membuka/tutup submenu. */
const navDropdowns = Array.from(document.querySelectorAll("[data-nav-dropdown]"));

function closeDropdown(dropdown, { returnFocus = false } = {}) {
    if (!dropdown.hasAttribute("data-open")) return;

    dropdown.removeAttribute("data-open");
    const submenu = dropdown.querySelector(".nav-submenu");
    const caret = dropdown.querySelector(".nav-caret");
    if (submenu) submenu.hidden = true;
    caret?.setAttribute("aria-expanded", "false");
    if (returnFocus) caret?.focus();
}

function closeAllDropdowns() {
    navDropdowns.forEach((dropdown) => closeDropdown(dropdown));
}

navDropdowns.forEach((dropdown) => {
    const caret = dropdown.querySelector(".nav-caret");

    caret?.addEventListener("click", (event) => {
        // Hentikan propagasi agar handler dokumen tidak langsung menutupnya kembali.
        event.stopPropagation();

        const shouldOpen = !dropdown.hasAttribute("data-open");
        closeAllDropdowns();

        if (shouldOpen) {
            const submenu = dropdown.querySelector(".nav-submenu");
            dropdown.setAttribute("data-open", "");
            if (submenu) submenu.hidden = false;
            caret.setAttribute("aria-expanded", "true");
        }
    });
});

menu?.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest("a")) {
        // Menutup dropdown juga penting untuk tautan anchor sesama halaman
        // (mis. dari /akademik ke /akademik#program-keahlian tanpa reload).
        closeAllDropdowns();
        closeMenu();
    }
});

document.addEventListener("click", (event) => {
    // event.target bisa saja sudah dilepas dari DOM oleh handler lain sebelum
    // event menggelegak ke dokumen (mis. ikon burger yang diganti innerHTML).
    // composedPath() merekam jalur dispatch ASLI sehingga pemeriksaan
    // "di dalam nav / di dalam dropdown" tetap akurat.
    const path = typeof event.composedPath === "function" ? event.composedPath() : [];
    const insideNav = nav ? path.includes(nav) : false;

    navDropdowns.forEach((dropdown) => {
        if (!path.includes(dropdown)) {
            closeDropdown(dropdown);
        }
    });

    if (mobileNavigation.matches && menu?.classList.contains("open") && !insideNav) {
        closeMenu();
    }
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;

    const menuIsOpen = menu?.classList.contains("open") ?? false;
    const activeElement = document.activeElement;
    const dropdownOfFocus =
        activeElement instanceof Element ? activeElement.closest("[data-nav-dropdown]") : null;
    const focusWasInSubmenu =
        activeElement instanceof Element && activeElement.closest(".nav-submenu") !== null;

    closeAllDropdowns();

    // Pertahankan fokus keyboard: kembalikan ke tombol panah submenu.
    if (dropdownOfFocus && (focusWasInSubmenu || activeElement?.classList.contains("nav-caret"))) {
        dropdownOfFocus.querySelector(".nav-caret")?.focus();
    }

    if (menuIsOpen) closeMenu({ returnFocus: true });
});

mobileNavigation.addEventListener("change", (event) => {
    if (!event.matches) {
        closeMenu();
        closeAllDropdowns();
    }
});

/* Tombol salin alamat di halaman kontak. */
const copyAddressButton = document.querySelector("[data-copy-address]");

copyAddressButton?.addEventListener("click", async () => {
    const address = document.querySelector("#fullAddress")?.textContent.trim();
    if (!address) return;

    try {
        await navigator.clipboard.writeText(address);
        copyAddressButton.textContent = "Alamat tersalin";
        copyAddressButton.setAttribute("aria-label", "Alamat berhasil disalin");
    } catch {
        const temporaryInput = document.createElement("textarea");
        temporaryInput.value = address;
        temporaryInput.setAttribute("readonly", "");
        temporaryInput.className = "contact-copy-buffer";
        document.body.append(temporaryInput);
        temporaryInput.select();
        const copied = document.execCommand("copy");
        temporaryInput.remove();
        copyAddressButton.textContent = copied
            ? "Alamat tersalin"
            : "Salin alamat";
    }

    window.setTimeout(() => {
        copyAddressButton.textContent = "Salin alamat";
        copyAddressButton.removeAttribute("aria-label");
    }, 2200);
});

/* Cegah kirim ganda pada formulir kontak. */
document.querySelectorAll(".contact-form").forEach((form) => {
    form.addEventListener("submit", () => {
        if (!form.reportValidity()) return;
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = true;
        button.setAttribute("aria-busy", "true");
        button.textContent = "Mengirim pesan…";
    });
});
