<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class SchoolSettings
{
    public static function contact(): array
    {
        $defaults = [
            'name' => 'SMK INFOKOM',
            'address' => [
                'Alamat lengkap' => 'Jl. Raya Cibinong No. 88, Kelurahan Cibinong, Kecamatan Cibinong',
                'RT/RW' => '003/012',
                'Kelurahan/Desa' => 'Cibinong',
                'Kecamatan' => 'Cibinong',
                'Kota/Kabupaten' => 'Bogor',
                'Provinsi' => 'Jawa Barat',
                'Kode pos' => '16915',
            ],
            'phones' => [
                'general' => '+62 812-3456-7890',
                'administration' => '+62 812-3456-7891',
                'ppdb' => '+62 812-3456-7892',
                'industry' => '+62 812-3456-7893',
                'alumni' => '+62 812-3456-7894',
            ],
            'whatsapp' => [
                'general' => '+62 812-3456-7890',
                'ppdb' => '+62 812-3456-7892',
            ],
            'emails' => [
                'general' => 'info@smkinfokom.sch.id',
                'ppdb' => 'ppdb@smkinfokom.sch.id',
                'administration' => 'admin@smkinfokom.sch.id',
                'industry' => 'industry@smkinfokom.sch.id',
                'alumni' => 'alumni@smkinfokom.sch.id',
            ],
            'maps_url' => 'https://maps.google.com/?q=SMK%20INFOKOM%20Bogor',
            'socials' => [
                'Instagram' => 'https://instagram.com/smkinfokom',
                'TikTok' => 'https://tiktok.com/@smkinfokom',
                'Facebook' => 'https://facebook.com/smkinfokom',
                'YouTube' => 'https://youtube.com/@smkinfokom',
                'Kanal resmi lainnya' => 'https://smkinfokom.sch.id',
            ],
            'hours' => [
                ['day' => 'Senin-Kamis', 'time' => '07.00-15.00'],
                ['day' => 'Jumat', 'time' => '07.00-11.30'],
                ['day' => 'Sabtu', 'time' => '07.00-13.00'],
                ['day' => 'Minggu', 'time' => 'Tutup'],
            ],
            'inquiry_types' => [
                'Umum',
                'PPDB',
                'Administrasi',
                'Kerja sama',
                'Hubungan industri',
                'Alumni',
                'Lainnya',
            ],
        ];

        $merged = array_replace_recursive($defaults, config('school.contact', []));

        if (! Schema::hasTable('site_settings')) {
            return $merged;
        }

        $mapping = [
            'school_name' => 'name',
            'school_address_line' => 'address.Alamat lengkap',
            'school_address_rtrw' => 'address.RT/RW',
            'school_address_village' => 'address.Kelurahan/Desa',
            'school_address_district' => 'address.Kecamatan',
            'school_address_city' => 'address.Kota/Kabupaten',
            'school_address_province' => 'address.Provinsi',
            'school_address_postal_code' => 'address.Kode pos',
            'school_phone_general' => 'phones.general',
            'school_phone_admin' => 'phones.administration',
            'school_phone_ppdb' => 'phones.ppdb',
            'school_phone_industry' => 'phones.industry',
            'school_phone_alumni' => 'phones.alumni',
            'school_whatsapp_general' => 'whatsapp.general',
            'school_whatsapp_ppdb' => 'whatsapp.ppdb',
            'school_email_general' => 'emails.general',
            'school_email_ppdb' => 'emails.ppdb',
            'school_email_admin' => 'emails.administration',
            'school_email_industry' => 'emails.industry',
            'school_email_alumni' => 'emails.alumni',
            'school_maps_url' => 'maps_url',
            'school_instagram_url' => 'socials.Instagram',
            'school_tiktok_url' => 'socials.TikTok',
            'school_facebook_url' => 'socials.Facebook',
            'school_youtube_url' => 'socials.YouTube',
            'school_other_social_url' => 'socials.Kanal resmi lainnya',
        ];

        // Seluruh pengaturan diambil dengan satu kueri, bukan satu kueri per kunci.
        $stored = SiteSetting::pluck('value', 'key');

        foreach ($mapping as $dbKey => $path) {
            $value = $stored[$dbKey] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            self::setValueByPath($merged, $path, $value);
        }

        $hoursRaw = $stored['school_hours'] ?? null;
        if (filled($hoursRaw)) {
            $hours = collect(preg_split('/\r\n|\r|\n/', $hoursRaw))
                ->map(function ($row) {
                    $parts = array_pad(explode('|', trim((string) $row), 2), 2, '');
                    if (! trim((string) $parts[0]) && ! trim((string) $parts[1])) {
                        return null;
                    }

                    return ['day' => trim((string) $parts[0]), 'time' => trim((string) $parts[1])];
                })
                ->filter()
                ->values()
                ->all();

            if ($hours !== []) {
                $merged['hours'] = $hours;
            }
        }

        return $merged;
    }

    /**
     * Data dinamis untuk halaman profil (/profil).
     *\     * Default sengaja persis sama dengan teks yang selama ini hardcoded di
     * view, sehingga tampilan tidak berubah sampai admin mengisi pengaturan.
     * Setiap baris di database diinterpretasikan sebagai satu butir
     * (paragraf sambutan, poin misi, satu fasilitas, atau satu tonggak sejarah).
     */
    public static function profile(): array
    {
        $profile = [
            'status' => 'Swasta',
            'npsn' => '',
            'founded_year' => '',
            'accreditation' => '',
            'principal_name' => '',
            'principal_title' => 'Kepala SMK INFOKOM',
            'sambutan_title' => 'Selamat Datang di SMK INFOKOM',
            'sambutan_paragraphs' => [
                "Assalamu'alaikum Warahmatullahi Wabarakatuh.",
                'Selamat datang di website resmi SMK INFOKOM. Website ini kami hadirkan sebagai sarana informasi dan komunikasi bagi siswa, orang tua, calon peserta didik, alumni, dunia industri, serta masyarakat.',
                'SMK INFOKOM berkomitmen menyelenggarakan pendidikan kejuruan yang relevan dengan perkembangan teknologi dan kebutuhan dunia kerja. Kami mendorong setiap peserta didik untuk tidak hanya menguasai pengetahuan, tetapi juga memiliki keterampilan, karakter, kreativitas, dan kesiapan menghadapi dunia industri.',
                'Semoga website ini dapat memberikan informasi yang bermanfaat mengenai SMK INFOKOM dan menjadi jembatan untuk mengenal sekolah kami lebih dekat.',
            ],
            'vision' => 'Menjadi sekolah kejuruan yang unggul, berkarakter, adaptif terhadap perkembangan teknologi, dan relevan dengan kebutuhan dunia industri.',
            'mission' => [
                'Menyelenggarakan pembelajaran kejuruan yang berkualitas dan relevan.',
                'Mengembangkan kompetensi siswa sesuai kebutuhan dunia industri.',
                'Membangun karakter disiplin, bertanggung jawab, kreatif, dan profesional.',
                'Mendorong pemanfaatan teknologi dalam proses pembelajaran.',
                'Membangun kemitraan yang kuat dengan dunia usaha dan dunia industri.',
            ],
            'facilities' => [
                ['title' => 'Laboratorium Komputer', 'description' => 'Ruang praktik untuk pembelajaran teknologi informasi dan komputasi.'],
                ['title' => 'Ruang Kelas', 'description' => 'Lingkungan belajar yang nyaman dan mendukung proses pembelajaran.'],
                ['title' => 'Perpustakaan', 'description' => 'Sumber bacaan dan referensi untuk mendukung tugas dan proyek siswa.'],
                ['title' => 'Bengkel / Workshop', 'description' => 'Tempat praktik kejuruan sesuai bidang keahlian masing-masing.'],
                ['title' => 'Ruang Praktik', 'description' => 'Fasilitas praktik khusus untuk proyek dan tugas kejuruan.'],
                ['title' => 'Aula', 'description' => 'Ruang pertemuan, upacara, dan kegiatan sekolah bersama.'],
                ['title' => 'Lapangan', 'description' => 'Area olahraga dan kegiatan fisik luar ruangan.'],
                ['title' => 'UKS', 'description' => 'Unit Kesehatan Sekolah untuk layanan kesehatan siswa.'],
                ['title' => 'Musala', 'description' => 'Ruang ibadah untuk aktivitas keagamaan dan spiritual siswa.'],
                ['title' => 'Kantin', 'description' => 'Fasilitas wadah untuk kebutuhan sehari-hari siswa dan guru.'],
            ],
            // Riwayat sekolah. Bawaan sengaja sama dengan teks placeholder yang
            // selama ini hardcoded di view (/profil #sejarah), sehingga tampilan
            // tidak berubah sampai admin mengisi pengaturan.
            'history' => [
                [
                    'year' => 'Data resmi',
                    'title' => 'Riwayat sekolah sedang dilengkapi.',
                    'description' => 'Informasi tahun pendirian dan tonggak perkembangan akan ditampilkan setelah dikonfirmasi oleh pihak sekolah.',
                    'pending' => true,
                ],
            ],
        ];

        if (! Schema::hasTable('site_settings')) {
            return self::withIdentity($profile);
        }

        $mapping = [
            'profile_status' => 'status',
            'profile_npsn' => 'npsn',
            'profile_founded_year' => 'founded_year',
            'profile_accreditation' => 'accreditation',
            'profile_principal_name' => 'principal_name',
            'profile_principal_title' => 'principal_title',
            'profile_sambutan_title' => 'sambutan_title',
            'profile_vision' => 'vision',
        ];

        // Seluruh pengaturan diambil dengan satu kueri, bukan satu kueri per kunci.
        $stored = SiteSetting::pluck('value', 'key');

        foreach ($mapping as $dbKey => $field) {
            $value = $stored[$dbKey] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $profile[$field] = $value;
        }

        $listMapping = [
            'profile_sambutan_text' => 'sambutan_paragraphs',
            'profile_mission' => 'mission',
        ];

        foreach ($listMapping as $dbKey => $field) {
            $lines = self::lines($stored[$dbKey] ?? null);
            if ($lines !== []) {
                $profile[$field] = $lines;
            }
        }

        $facilities = collect(self::lines($stored['profile_facilities'] ?? null))
            ->map(function (string $row) {
                $parts = array_pad(explode('|', $row, 2), 2, '');

                return [
                    'title' => trim($parts[0]),
                    'description' => trim($parts[1]),
                ];
            })
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values()
            ->all();

        if ($facilities !== []) {
            $profile['facilities'] = $facilities;
        }

        // Riwayat sekolah: satu baris = satu tonggak dengan format
        // "Tahun|Judul|Keterangan". Baris kosong diabaikan.
        $history = collect(self::lines($stored['profile_history'] ?? null))
            ->map(function (string $row) {
                $parts = array_pad(explode('|', $row, 3), 3, '');

                return [
                    'year' => trim($parts[0]),
                    'title' => trim($parts[1]),
                    'description' => trim($parts[2]),
                    'pending' => false,
                ];
            })
            ->filter(fn (array $entry) => $entry['year'] !== '' || $entry['title'] !== '')
            ->values()
            ->all();

        if ($history !== []) {
            $profile['history'] = $history;
        }

        return self::withIdentity($profile);
    }

    /**
     * Identitas sekolah untuk halaman profil: nama, alamat, dan email
     * diambil dari pengaturan kontak yang sudah ada agar tidak diduplikasi.
     */
    protected static function withIdentity(array $profile): array
    {
        $contact = self::contact();

        $address = collect($contact['address'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->implode(', ');

        $profile['identity'] = [
            ['label' => 'Nama Sekolah', 'value' => $contact['name'] ?? '', 'fallback' => 'Belum dikonfirmasi'],
            ['label' => 'Status Sekolah', 'value' => $profile['status'], 'fallback' => 'Belum dikonfirmasi'],
            ['label' => 'NPSN', 'value' => $profile['npsn'], 'fallback' => 'Belum dikonfirmasi'],
            ['label' => 'Tahun Berdiri', 'value' => $profile['founded_year'], 'fallback' => 'Belum dikonfirmasi'],
            ['label' => 'Akreditasi', 'value' => $profile['accreditation'], 'fallback' => 'Belum dikonfirmasi'],
            ['label' => 'Kepala Sekolah', 'value' => $profile['principal_name'], 'fallback' => 'Informasi belum tersedia'],
            ['label' => 'Alamat', 'value' => $address, 'fallback' => 'Lengkapi melalui pengaturan sekolah'],
            ['label' => 'Email', 'value' => $contact['emails']['general'] ?? '', 'fallback' => 'Lengkapi melalui pengaturan sekolah'],
        ];

        // Nama sekolah untuk salinan naratif halaman profil (bagian "tentang"
        // dan CTA), agar tidak lagi hardcoded di view.
        $profile['school_name'] = filled($contact['name'] ?? '') ? $contact['name'] : 'SMK INFOKOM';

        // Fakta singkat untuk hero profil: hanya baris identitas yang
        // nilainya sudah terisi, sehingga data kosong tidak ikut tampil.
        $quickFactLabels = ['Status Sekolah', 'NPSN', 'Tahun Berdiri', 'Akreditasi'];
        $profile['quick_facts'] = collect($profile['identity'])
            ->filter(fn (array $item) => in_array($item['label'], $quickFactLabels, true) && filled($item['value']))
            ->values()
            ->all();

        return $profile;
    }

    /**
     * @return array<int, string>
     */
    protected static function lines(?string $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    protected static function setValueByPath(array &$target, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $current = &$target;

        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $current[$segment] = $value;

                return;
            }

            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }
    }
}
