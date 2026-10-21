<?php

namespace App\Support;

/**
 * Daftar sebelas bagian situs publik: urutan tampil, judul, pengantar,
 * eyebrow, dan tautan rutenya. Menjadi satu-satunya sumber metadata
 * bagi halaman seksi, pusat informasi beranda, sitemap.xml, dan data
 * terstruktur Schema.org — sehingga teks dan urutannya tidak pernah
 * berbeda antarhalaman.
 */
class SiteSections
{
    /**
     * Urutan tampil di pusat informasi beranda: sepuluh bagian melewati
     * rute tangkap-lambung /{section}, Galeri memakai rute sendiri.
     */
    public const ORDER = [
        'profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang',
        'ppdb', 'informasi', 'galeri', 'alumni', 'kontak',
    ];

    /**
     * Metadata judul/pengantar/eyebrow untuk sepuluh bagian yang dilayani
     * rute tangkap-lambung /{section}.
     *
     * @return array<string, array{title: string, intro: string, eyebrow: string}>
     */
    public static function pages(): array
    {
        return [
            'profil' => ['title' => 'Profil sekolah', 'intro' => 'Mengenal SMK INFOKOM lebih dekat: lingkungan belajar, visi, misi, dan nilai yang membentuk lulusan siap berkarya.', 'eyebrow' => 'Tentang sekolah'],
            'akademik' => ['title' => 'Program keahlian', 'intro' => 'Pilih jalur belajar yang sesuai minat dan bangun keterampilan melalui praktik, proyek, dan pengalaman industri.', 'eyebrow' => 'Akademik'],
            'kesiswaan' => ['title' => 'Kesiswaan', 'intro' => 'Kegiatan, ruang berkembang, dan pencapaian siswa di dalam maupun di luar kelas.', 'eyebrow' => 'Kehidupan siswa'],
            'prestasi' => ['title' => 'Prestasi siswa', 'intro' => 'Apresiasi untuk kerja keras dan karya siswa di berbagai tingkat kompetisi.', 'eyebrow' => 'Karya dan pencapaian'],
            'industri' => ['title' => 'Mitra dunia industri', 'intro' => 'Kolaborasi yang membuka pengalaman praktik, wawasan profesi, dan kesiapan kerja.', 'eyebrow' => 'Kolaborasi'],
            'jepang' => ['title' => 'Program Magang Jepang', 'intro' => 'Kenali tahapan persiapan, seleksi, dan pembekalan sebelum mengikuti program magang.', 'eyebrow' => 'Peluang internasional'],
            'ppdb' => ['title' => 'Penerimaan murid baru', 'intro' => 'Informasi terbaru mengenai pendaftaran, jadwal, dan tahapan seleksi calon murid.', 'eyebrow' => 'PPDB / SPMB'],
            'informasi' => ['title' => 'Informasi sekolah', 'intro' => 'Berita, pengumuman, dan agenda terbaru dari SMK INFOKOM.', 'eyebrow' => 'Kabar terkini'],
            'alumni' => ['title' => 'Alumni dan tracer study', 'intro' => 'Cerita perjalanan alumni dan gambaran aktivitas setelah menyelesaikan pendidikan.', 'eyebrow' => 'Jejak lulusan'],
            'kontak' => ['title' => 'Hubungi sekolah', 'intro' => 'Pilih jalur komunikasi yang sesuai. Kami siap membantu pertanyaan tentang sekolah, PPDB, administrasi, dan kerja sama.', 'eyebrow' => 'Kontak SMK INFOKOM'],
        ];
    }

    /**
     * Sebelas bagian lengkap dengan tautan rute masing-masing, sesuai
     * urutan ORDER. Kuncinya adalah slug bagian ('profil', 'galeri', ...).
     *
     * @return array<string, array{title: string, intro: string, eyebrow: string, href: string}>
     */
    public static function all(): array
    {
        $pages = self::pages();

        $pages['galeri'] = [
            'title' => 'Galeri kegiatan',
            'intro' => 'Dokumentasi kegiatan belajar, praktik, dan karya siswa SMK INFOKOM.',
            'eyebrow' => 'Dokumentasi',
        ];

        return collect(self::ORDER)
            ->mapWithKeys(fn (string $key) => [$key => [
                ...$pages[$key],
                'href' => $key === 'galeri' ? route('galeri.index') : route('site.section', $key),
            ]])
            ->all();
    }
}
