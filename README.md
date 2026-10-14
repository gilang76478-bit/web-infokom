# SMK INFOKOM — Situs Sekolah + Panel Admin

Situs publik dan panel admin untuk SMK INFOKOM (sekolah vokasi, program keahlian teknologi & budaya Jepang), dibangun dengan **Laravel + Blade + Vite + CSS murni** (tanpa framework CSS/JS eksternal).

## Fitur

### Situs publik
- Beranda: hero, statistik, program keahlian, berita, galeri, alumni, jalur PPDB.
- Halaman seksi: `profil`, `akademik`, `kesiswaan`, `prestasi`, `industri`, `jepang`, `ppdb`, `informasi`, `galeri`, `alumni`, `kontak`.
- Formulir **pendaftaran PPDB** (`/ppdb`) dan **pesan kontak** (`/kontak`) dengan proteksi rate-limit.
- Tema terang/gelap otomatis mengikuti preferensi sistem (`prefers-color-scheme`).

### Panel admin (`/admin`)
- Login dengan throttling (5 percobaan/menit).
- Dashboard metrik (PPDB, pesan, konten).
- Kelola PPDB: ubah status pendaftar.
- Kelola pesan kontak: tandai sudah dibaca.
- Kelola konten (CRUD): akademik, berita, agenda, prestasi, galeri, jepang, industri, alumni, tracer, pengumuman.
- Pengaturan sekolah: kontak resmi (telepon, WhatsApp, email, alamat).

## Struktur

```
app/
  Http/Controllers/
    SiteController.php            # Beranda + halaman seksi (data dari model/setting)
    AdminAuthController.php       # Login/logout admin
    AdminController.php           # Dashboard metrik
    AdminContentController.php    # CRUD konten (TYPES + fields + rules)
    AdminContactController.php    # Pesan kontak
    AdminPpdbController.php       # Pendaftar PPDB
    AdminSettingsController.php   # Pengaturan sekolah
    ContactMessageController.php  # Store pesan kontak (publik)
    PpdbApplicantController.php   # Store pendaftar PPDB (publik)
resources/
  css/
    site.css                      # Entry situs publik  -> site/base.css + site/public.css
    admin.css                     # Entry panel admin    -> site/base.css + site/admin.css
    site/
      base.css                    # Token desain (:root, dark mode), reset, tombol
      public.css                  # Nav, beranda, halaman seksi, footer
      admin.css                   # Sidebar, topbar, tabel, form, settings
  js/
    site.js                       # Nav mobile, dropdown, salin alamat, form kontak
    admin.js                      # Sidebar toggle, filter pencarian, dialog hapus
  views/
    layouts/                      # site / admin / admin-guest (header-footer dipusatkan)
    pages/                        # Beranda
    sections/                     # Halaman seksi publik
    admin/                        # Dashboard, login, ppdb, pesan, konten, settings
      partials/                   # Sidebar & topbar admin
routes/web.php                     # Semua route + nama route
database/seeders/                  # SiteSeeder (data contoh) + data/
tests/Feature/ExampleTest.php      # Uji end-to-end halaman & formulir
```

## Setup

Prasyarat: PHP ≥ 8.3, Composer, Node.js.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Situs: <http://127.0.0.1:8000> · Panel admin: <http://127.0.0.1:8000/admin/login>
(akun admin dari seeder; lihat `database/seeders/`).

## Perintah penting

| Perintah | Fungsi |
| --- | --- |
| `npm run dev` | Vite dev server (HMR) |
| `npm run build` | Build aset produksi |
| `php artisan test` | Jalankan seluruh tes fitur |
| `composer test` | Bersihkan config lalu tes |
| `php artisan migrate:fresh --seed` | Reset database + data contoh |

## Konvensi

- **Nama route tidak berubah** — dipakai luas di view dan tes.
- CSS dipecah per konteks (publik / admin / token bersama); jangan menaruh gaya admin di `public.css`.
- View wajib `@extends` layout yang sesuai (`layouts.site`, `layouts.admin`, `layouts.admin-guest`) — header/footer/nav tidak boleh diduplikasi.
