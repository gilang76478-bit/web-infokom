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

/* Tombol cetak formulir PPDB (/ppdb/formulir): memakai dialog cetak
   bawaan peramban; lembar formulir sendiri sudah bebas navigasi lewat
   gaya @media print. */
document.querySelectorAll("[data-print-form]").forEach((button) => {
    button.addEventListener("click", () => window.print());
});

/* Scrollspy pita jangkar profil: menyorot bagian yang sedang dibaca.
   Pita (data-scrollspy) menetap di bawah pita navigasi utama; tiap guliran
   menentukan bagian terakhir yang melewati garis baca di bawah kedua pita. */
const scrollSpy = document.querySelector("[data-scrollspy]");

if (scrollSpy) {
    const spyEntries = Array.from(scrollSpy.querySelectorAll('a[href^="#"]'))
        .map((link) => ({ link, target: document.querySelector(link.getAttribute("href")) }))
        .filter((entry) => entry.target !== null);

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    const markSpy = (current) => {
        // Di layar sempit pita digeser mendatar; bawa pil aktif ke tengah.
        const scrollsSideways = scrollSpy.scrollWidth > scrollSpy.clientWidth + 4;

        spyEntries.forEach(({ link }) => {
            const active = current !== null && link === current.link;
            link.classList.toggle("is-active", active);

            if (!active) {
                link.removeAttribute("aria-current");
                return;
            }

            link.setAttribute("aria-current", "location");
            if (scrollsSideways) {
                link.scrollIntoView({
                    behavior: reduceMotion.matches ? "auto" : "smooth",
                    inline: "center",
                    block: "nearest",
                });
            }
        });
    };

    let spyFrame = 0;
    const updateSpy = () => {
        spyFrame = 0;

        // Garis baca: sedikit di bawah pita navigasi utama + pita jangkar.
        // Dihitung dari tinggi kedua pita yang sebenarnya agar tetap akurat
        // bila tinggi berubah; harus berada tepat di bawah titik pendaratan
        // anchor (scroll-padding-top) supaya bagian yang baru dibuka langsung
        // ikut aktif.
        const readingLine =
            window.scrollY +
            (nav?.offsetHeight ?? 76) +
            scrollSpy.offsetHeight +
            32;
        let current = spyEntries[0] ?? null;

        for (const entry of spyEntries) {
            if (entry.target.getBoundingClientRect().top + window.scrollY <= readingLine) {
                current = entry;
            }
        }

        markSpy(current);
    };

    const requestSpyUpdate = () => {
        if (!spyFrame) spyFrame = window.requestAnimationFrame(updateSpy);
    };

    window.addEventListener("scroll", requestSpyUpdate, { passive: true });
    window.addEventListener("resize", requestSpyUpdate);
    updateSpy();
}

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

/* ============================================================
   Animasi global (Tahap 2): keputusan aman — JavaScript hanya
   MENANDAI elemen yang akan dianimasikan. Bila JS gagal, kelas
   .has-reveal tidak pernah dipasang dan seluruh konten tampil
   normal tanpa animasi. Reduced motion tidak dianimasikan sama
   sekali (CSS global di responsive.css sudah mematikannya juga).
   ============================================================ */

/* Pita navigasi: sorotan halus saat halaman digulir. */
if (nav) {
    const syncScrolledNav = () => nav.classList.toggle("is-scrolled", window.scrollY > 8);
    syncScrolledNav();
    window.addEventListener("scroll", syncScrolledNav, { passive: true });
}

try {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    if (!reduceMotion.matches && "IntersectionObserver" in window) {
        const targets = new Set();

        /* Judul section: baris judul, atau judul mandiri di luar baris. */
        document.querySelectorAll(".sh-row").forEach((element) => targets.add(element));
        document.querySelectorAll(".sh").forEach((element) => {
            if (!element.closest(".sh-row")) targets.add(element);
        });

        /* Anak sebuah grid/kartu tampil bertahap (stagger per baris),
           maksimal jeda 350ms agar tidak menunda konten terakhir. */
        document
            .querySelectorAll("[class*='grid'], .home-programs, .home-news, .home-gallery, .detail-list")
            .forEach((group) => {
                Array.from(group.children).forEach((child, index) => {
                    if (targets.has(child)) return;
                    child.classList.add("motion");
                    child.style.setProperty("--motion-delay", `${Math.min(index, 5) * 70}ms`);
                    targets.add(child);
                });
            });

        /* Blok mandiri di luar grid. */
        document
            .querySelectorAll(
                ".profile-panel, .academic-intro-panel, .industry-intro-panel, .ppdb-intro-panel, " +
                    ".home-japan, .home-achievement, .org-card"
            )
            .forEach((element) => {
                if (targets.has(element)) return;
                element.classList.add("motion");
                targets.add(element);
            });

        if (targets.size > 0) {
            document.documentElement.classList.add("has-reveal");

            /* Muncul sekali saat masuk viewport; tidak diulang ketika
               pengguna menggulir naik-turun. */
            const revealObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add("is-in");
                        revealObserver.unobserve(entry.target);
                    });
                },
                { rootMargin: "0px 0px -6% 0px", threshold: 0.1 }
            );

            targets.forEach((target) => revealObserver.observe(target));
        }
    }
} catch {
    /* Animasi bersifat pelengkap: kegagalan tidak boleh menyembunyikan konten. */
}

/* ============================================================
   Carousel (cerita alumni): rel geser snap + tombol panah yang
   menggeser halus satu kartu; tombol dinonaktifkan di ujung rel.
   ============================================================ */
document.querySelectorAll("[data-carousel]").forEach((carousel) => {
    const viewport = carousel.querySelector("[data-carousel-viewport]");
    const prev = carousel.querySelector("[data-carousel-prev]");
    const next = carousel.querySelector("[data-carousel-next]");
    if (!(viewport instanceof HTMLElement)) return;

    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)");

    const stepSize = () => {
        const first = viewport.firstElementChild?.firstElementChild;
        const width = first instanceof HTMLElement ? first.getBoundingClientRect().width : 320;
        return width + 16;
    };

    const glide = (direction) => {
        viewport.scrollBy({
            left: direction * stepSize(),
            behavior: reduce.matches ? "auto" : "smooth",
        });
    };

    prev?.addEventListener("click", () => glide(-1));
    next?.addEventListener("click", () => glide(1));

    const sync = () => {
        if (prev instanceof HTMLButtonElement) prev.disabled = viewport.scrollLeft <= 4;
        if (next instanceof HTMLButtonElement) {
            next.disabled = viewport.scrollLeft >= viewport.scrollWidth - viewport.clientWidth - 4;
        }
    };

    viewport.addEventListener("scroll", sync, { passive: true });
    window.addEventListener("resize", sync);
    sync();
});

/* ============================================================
   Lightbox galeri — klik foto untuk memperbesar dengan animasi
   tumbuh; panah, tombol keyboard, dan usapan jari (swipe)
   menggeser ke foto berikutnya/sebelumnya.
   ============================================================ */
const lightboxItems = Array.from(document.querySelectorAll("[data-lightbox-item]"));

if (lightboxItems.length > 0) {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    const entries = lightboxItems.map((figure) => {
        const thumb = figure.querySelector("img");

        figure.setAttribute("role", "button");
        figure.setAttribute("tabindex", "0");
        figure.setAttribute("aria-label", `Perbesar foto: ${thumb?.alt ?? ""}`.trim());

        return {
            src: thumb?.currentSrc || thumb?.src || "",
            caption:
                figure.querySelector("figcaption b")?.textContent?.trim() ||
                thumb?.alt ||
                "",
        };
    });

    const box = document.createElement("div");
    box.className = "lightbox";
    box.hidden = true;
    box.setAttribute("role", "dialog");
    box.setAttribute("aria-modal", "true");
    box.setAttribute("aria-label", "Tampilan penuh foto galeri");
    box.innerHTML = `
        <div class="lightbox-backdrop" data-lb-close></div>
        <figure class="lightbox-stage">
            <img alt="">
            <figcaption class="lightbox-caption"><span class="lightbox-count"></span><b></b></figcaption>
        </figure>
        <button class="lightbox-btn lightbox-prev" type="button" data-lb-prev aria-label="Foto sebelumnya">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        </button>
        <button class="lightbox-btn lightbox-next" type="button" data-lb-next aria-label="Foto berikutnya">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <button class="lightbox-close" type="button" data-lb-close aria-label="Tutup tampilan penuh">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>`;
    document.body.append(box);

    const stageImg = box.querySelector(".lightbox-stage img");
    const count = box.querySelector(".lightbox-count");
    const caption = box.querySelector(".lightbox-caption b");
    const prevButton = box.querySelector("[data-lb-prev]");
    const nextButton = box.querySelector("[data-lb-next]");

    let index = 0;
    let slideTimer = 0;
    let lastFocused = null;

    const paint = (direction) => {
        const entry = entries[index];
        if (!entry || !stageImg) return;

        if (count) count.textContent = `${index + 1} / ${entries.length}`;
        if (caption) caption.textContent = entry.caption;

        if (!direction || reduceMotion.matches) {
            stageImg.src = entry.src;
            stageImg.alt = entry.caption;
            return;
        }

        const outClass = direction > 0 ? "is-out-left" : "is-out-right";
        const inClass = direction > 0 ? "is-in-right" : "is-in-left";

        stageImg.classList.add(outClass);
        window.clearTimeout(slideTimer);
        slideTimer = window.setTimeout(() => {
            stageImg.src = entry.src;
            stageImg.alt = entry.caption;
            stageImg.classList.remove(outClass);
            stageImg.classList.add(inClass);
            window.setTimeout(() => stageImg.classList.remove(inClass), 340);
        }, 150);
    };

    const open = (nextIndex, opener) => {
        index = nextIndex;
        lastFocused = opener instanceof HTMLElement ? opener : null;
        box.hidden = false;
        document.documentElement.classList.add("lightbox-open");
        paint(0);
        if (nextButton instanceof HTMLElement) nextButton.focus();
    };

    const close = () => {
        box.hidden = true;
        document.documentElement.classList.remove("lightbox-open");
        if (lastFocused instanceof HTMLElement) lastFocused.focus();
    };

    const step = (delta) => {
        if (entries.length < 2) return;
        index = (index + delta + entries.length) % entries.length;
        paint(delta);
    };

    lightboxItems.forEach((figure, position) => {
        figure.addEventListener("click", () => open(position, figure));
        figure.addEventListener("keydown", (event) => {
            if (event.key !== "Enter" && event.key !== " ") return;
            event.preventDefault();
            open(position, figure);
        });
    });

    prevButton?.addEventListener("click", () => step(-1));
    nextButton?.addEventListener("click", () => step(1));
    box.querySelectorAll("[data-lb-close]").forEach((closer) => {
        closer.addEventListener("click", close);
    });

    document.addEventListener("keydown", (event) => {
        if (box.hidden) return;
        if (event.key === "Escape") close();
        else if (event.key === "ArrowLeft") step(-1);
        else if (event.key === "ArrowRight") step(1);
    });

    /* Usapan jari: geser ke kiri untuk lanjut, ke kanan untuk mundur. */
    let touchStartX = null;
    box.addEventListener(
        "touchstart",
        (event) => {
            touchStartX = event.changedTouches[0]?.clientX ?? null;
        },
        { passive: true },
    );
    box.addEventListener(
        "touchend",
        (event) => {
            if (touchStartX === null) return;
            const deltaX = (event.changedTouches[0]?.clientX ?? touchStartX) - touchStartX;
            touchStartX = null;
            if (Math.abs(deltaX) > 44) step(deltaX < 0 ? 1 : -1);
        },
        { passive: true },
    );
}

/* ============================================================
   Tirai halaman — saat tautan internal diklik, tirai warna sekolah
   naik menutup layar selama halaman baru dimuat. Murni pelengkap:
   tanpa JS, reduced motion, tautan eksternal, atau target blank —
   tirai tidak pernah aktif.
   ============================================================ */
try {
    const curtainMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const curtain = document.createElement("div");
    curtain.className = "page-curtain";
    curtain.setAttribute("aria-hidden", "true");
    document.body.append(curtain);

    document.addEventListener("click", (event) => {
        if (curtainMotion.matches || event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const path = typeof event.composedPath === "function" ? event.composedPath() : [];
        const link = path.find((node) => node instanceof HTMLAnchorElement);

        if (!(link instanceof HTMLAnchorElement)) return;
        if (link.target === "_blank" || link.hasAttribute("download")) return;

        let url;
        try {
            url = new URL(link.href, location.href);
        } catch {
            return;
        }

        if (url.origin !== location.origin) return;
        /* Anchor sesama halaman: biarkan gulir mulus tanpa tirai. */
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

        curtain.classList.add("is-active");
    });

    window.addEventListener("pageshow", () => curtain.classList.remove("is-active"));
} catch {
    /* Animasi pelengkap: kegagalan tidak mengganggu navigasi. */
}
