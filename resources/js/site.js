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

menu?.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest("a")) {
        menu.querySelectorAll(".nav-dropdown[open]").forEach((detail) => {
            detail.removeAttribute("open");
        });
        closeMenu();
    }
});

document.addEventListener("click", (event) => {
    const target = event.target instanceof Element ? event.target : null;

    document.querySelectorAll(".nav-dropdown[open]").forEach((detail) => {
        if (target && !detail.contains(target)) {
            detail.removeAttribute("open");
        }
    });

    if (
        mobileNavigation.matches &&
        menu?.classList.contains("open") &&
        target &&
        !nav?.contains(target)
    ) {
        closeMenu();
    }
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;

    const menuIsOpen = menu?.classList.contains("open") ?? false;
    document.querySelectorAll(".nav-dropdown[open]").forEach((detail) => {
        detail.removeAttribute("open");
    });

    if (menuIsOpen) closeMenu({ returnFocus: true });
});

mobileNavigation.addEventListener("change", (event) => {
    if (!event.matches) {
        closeMenu();
        document.querySelectorAll(".nav-dropdown[open]").forEach((detail) => {
            detail.removeAttribute("open");
        });
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
