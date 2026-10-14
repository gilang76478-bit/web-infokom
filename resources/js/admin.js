/* Skrip panel admin: sidebar mobile, pencarian halaman,
   dialog konfirmasi hapus, dan status tombol simpan. */
const sidebar = document.querySelector("#adminSidebar");
const sidebarToggle = document.querySelector("#adminMenuToggle");
const sidebarBackdrop = document.querySelector("#adminSidebarBackdrop");
const searchInput = document.querySelector("#adminSearch");

function closeSidebar() {
    sidebar?.classList.remove("is-open");
    sidebarToggle?.setAttribute("aria-expanded", "false");
    if (sidebarBackdrop) sidebarBackdrop.hidden = true;
}

sidebarToggle?.addEventListener("click", () => {
    const open = sidebar.classList.toggle("is-open");
    sidebarToggle.setAttribute("aria-expanded", String(open));
    sidebarBackdrop.hidden = !open;
});

sidebarBackdrop?.addEventListener("click", closeSidebar);
sidebar?.addEventListener("click", (event) => {
    if (event.target.closest("a")) closeSidebar();
});

function filterAdminItems() {
    const query = searchInput?.value.trim().toLocaleLowerCase("id-ID") ?? "";
    const items = [...document.querySelectorAll("[data-search-item]")];
    let visible = 0;

    items.forEach((item) => {
        const matches = item.textContent
            .toLocaleLowerCase("id-ID")
            .includes(query);
        item.hidden = !matches;
        if (matches) visible += 1;
    });

    const emptyState = document.querySelector(".admin-no-results");
    if (emptyState) emptyState.hidden = !query || visible > 0;

    const resultCount = document.querySelector(".admin-result-count");
    if (resultCount) {
        resultCount.textContent = query
            ? `${visible} hasil cocok`
            : `${resultCount.dataset.pageCount} dari ${resultCount.dataset.total}`;
    }
}

searchInput?.addEventListener("input", filterAdminItems);
searchInput?.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        searchInput.value = "";
        filterAdminItems();
        searchInput.blur();
    }
});

document.addEventListener("keydown", (event) => {
    const target = event.target;
    const editing =
        target instanceof HTMLElement &&
        ["INPUT", "TEXTAREA", "SELECT"].includes(target.tagName);

    if (event.key === "/" && !editing && searchInput) {
        event.preventDefault();
        searchInput.focus();
    }

    if (event.key === "Escape") closeSidebar();
});

const deleteDialog = document.createElement("dialog");
deleteDialog.className = "admin-confirm-dialog";
deleteDialog.setAttribute("aria-labelledby", "adminConfirmTitle");
deleteDialog.innerHTML = `
  <h2 id="adminConfirmTitle">Hapus data ini?</h2>
  <p id="adminConfirmMessage">Tindakan ini tidak dapat dibatalkan.</p>
  <div class="admin-confirm-actions">
    <button type="button" data-confirm-cancel>Batal</button>
    <button type="button" class="admin-confirm-delete" data-confirm-accept>Ya, hapus</button>
  </div>
`;
document.body.append(deleteDialog);

let pendingDeleteForm;
deleteDialog
    .querySelector("[data-confirm-cancel]")
    .addEventListener("click", () => deleteDialog.close());
deleteDialog
    .querySelector("[data-confirm-accept]")
    .addEventListener("click", () => {
        if (!pendingDeleteForm) return;
        pendingDeleteForm.dataset.confirmed = "true";
        pendingDeleteForm.requestSubmit();
    });
deleteDialog.addEventListener("close", () => {
    pendingDeleteForm = undefined;
});

document.querySelectorAll("form[data-confirm-delete]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (form.dataset.confirmed === "true") return;
        event.preventDefault();
        pendingDeleteForm = form;
        deleteDialog.querySelector("#adminConfirmMessage").textContent =
            form.dataset.confirmMessage;
        deleteDialog.showModal();
    });
});

document.querySelectorAll("form[data-admin-form]").forEach((form) => {
    form.addEventListener("submit", () => {
        const submit = form.querySelector('button[type="submit"]');
        if (!submit || !form.reportValidity()) return;
        submit.disabled = true;
        submit.setAttribute("aria-busy", "true");
        submit.dataset.originalLabel = submit.textContent;
        submit.textContent = "Menyimpan…";
    });
});

const statusMessage = document.querySelector(".admin-status[role='status']");
if (statusMessage) {
    statusMessage.setAttribute("tabindex", "-1");
    window.setTimeout(() => {
        statusMessage.classList.add("is-dismissed");
        window.setTimeout(() => statusMessage.remove(), 300);
    }, 6500);
}

filterAdminItems();
