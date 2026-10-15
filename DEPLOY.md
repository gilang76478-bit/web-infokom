# Panduan Deploy Produksi — Portal SMKS Infokom Bogor

Checklist langkah sebelum dan sesudah deploy. Perintah dijalankan dari root proyek.

## 1. Lingkungan (`.env` produksi)

- `APP_ENV=production`
- `APP_DEBUG=false` — **wajib**; halaman kustom `resources/views/errors/500.blade.php`
  hanya dipakai saat debug mati, dan debug=true membocorkan trace di publik.
- `APP_URL` = domain final (https), karena dipakai pembuatan URL absolut.
- `APP_KEY` sudah terisi (`php artisan key:generate --show` bila perlu).
- Jangan pernah menyalin `.env` lokal ke server; `.gitignore` sudah mengecualikannya.

## 2. Build aset frontend

```sh
npm ci            # pasang dependensi persis sesuai package-lock.json
npm run build     # bersih, tanpa warning; menghasilkan public/build/*
```

- Font web **self-hosted**: `laravel-vite-plugin/fonts` mengunduh Bricolage
  Grotesque & Plus Jakarta Sans sekali saat build lalu menyajikannya dari
  `/build` (tidak ada permintaan ke fonts.googleapis.com / gstatic).
- Bila jaringan build terbatas, cache unduhan font berada di
  `node_modules/.cache/laravel-vite-plugin/fonts` (ikut terpasang saat
  `npm install` bila cache tersedia).

## 3. Cache Laravel

```sh
php artisan optimize        # config + routes + views (+ filament/blade-icons)
```

Jalankan **setelah** build dan setelah semua file final. Saat mengembangkan
di lokal, kembalikan ke mode segar dengan `php artisan optimize:clear`.

## 4. Storage publik (unggahan foto galeri)

```sh
php artisan storage:link     # public/storage -> storage/app/public
chmod -R 775 storage bootstrap/cache   # Linux; jangan 777
```

Tanpa symlink ini, foto galeri hasil unggahan panel admin tidak terlayani.

## 5. Header cache aset (sudah dikonfigurasi di repo)

| Path        | Header                                              |
|-------------|-----------------------------------------------------|
| `/build/*`  | `public, max-age=31536000, immutable` (nama hash)   |
| `/storage/*`| `public, max-age=604800` (7 hari)                   |
| HTML        | `no-cache, private` (bawaan Laravel)                |

- **Apache** (`public/.htaccess`, sudah di repo): aturan `RewriteRule` +
  `Header ... env=`. Verifikasi:
  `curl -sI https://domain.tld/build/manifest.json | grep -i cache-control`
- **nginx** — tambahkan pada blok `server`:

  ```nginx
  location /build/ {
      add_header Cache-Control "public, max-age=31536000, immutable";
      try_files $uri =404;
  }
  location /storage/ {
      add_header Cache-Control "public, max-age=604800";
      try_files $uri =404;
  }
  location ~ /\.(?!well-known).* { deny all; }   # dotfile (.env, .git, dll.)
  ```

## 6. Keamanan dasar

- Middleware `App\Http\Middleware\SecurityHeaders` (terdaftar global di
  `bootstrap/app.php`) mengirim: `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`,
  `Permissions-Policy`, dan HSTS ketika request HTTPS.
- `public/.htaccess` menolak **semua file tersembunyi** (`(^|/)\.` → 403),
  termasuk yang berada di balik symlink `public/storage`.
- `Options -Indexes` sudah aktif (direktori tidak bisa dibrowsing).
- Panel admin berada di `/admin` (Filament) dan `/admin/*` kustom — semua
  dijaga middleware `auth` + `admin` (`is_admin`); jangan pernah
  menonaktifkan HTTPS di depannya.

## 7. Verifikasi pascadeploy

```sh
php artisan about                      # env & cache
curl -sI https://domain.tld/ | grep -i x-content-type   # header keamanan
curl -sI https://domain.tld/build/manifest.json | grep -i cache-control
curl -s -o /dev/null -w "%{http_code}" https://domain.tld/.env   # harap 403/404
php artisan test                       # 32 test harus lulus
```
