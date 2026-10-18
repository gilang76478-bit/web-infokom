<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Agenda;
use App\Models\Alumnus;
use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\JapanStep;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\TracerStat;
use App\Support\SchoolSettings;
use App\Support\WhatsApp;

class SiteController extends Controller
{
    public function home()
    {
        $site = $this->siteData();

        return view('pages.home', [
            'site' => $site,
            // Pusat informasi: sebelas bagian situs dengan tautan dan jumlah
            // dinamis dari data sekolah.
            'explore' => $this->exploreSections($site),
        ]);
    }

    public function section(string $section)
    {
        $pages = $this->sectionPages();

        abort_unless(isset($pages[$section]), 404);

        $data = match ($section) {
            'profil' => [
                ...SchoolSettings::profile(),
                'teachers' => Teacher::query()->with('program')->ordered()->get(),
            ],
            'akademik' => Program::query()->with('teachers')->orderBy('short')->get(),
            'kesiswaan' => [
                'achievements' => Achievement::orderByDesc('year')->get(),
                'activities' => GalleryItem::with('category')->orderBy('id')->get(),
            ],
            'prestasi' => Achievement::orderByDesc('year')->get(),
            'industri' => Partner::orderBy('name')->get(),
            'jepang' => JapanStep::orderBy('id')->get(),
            'ppdb' => [
                'posts' => Post::with('category')
                    ->whereHas('category', fn ($query) => $query->where('slug', 'ppdb'))
                    ->orderByDesc('published_at')
                    ->get(),
                'programs' => Program::orderBy('short')->get(),
            ],
            'informasi' => [
                'posts' => Post::with('category')->orderByDesc('published_at')->get(),
                'agendas' => Agenda::orderBy('date')->get(),
            ],
            'alumni' => [
                'alumni' => Alumnus::orderBy('id')->get(),
                'tracer' => TracerStat::orderBy('id')->get(),
            ],
            'kontak' => $this->contactData(),
            default => [],
        };

        return view('sections.show', compact('section', 'pages', 'data'));
    }

    /**
     * Metadata dasar tiap halaman seksi: judul, pengantar, dan eyebrow.
     * Dipakai bersama oleh halaman seksi dan pusat informasi di beranda
     * agar teks keduanya tidak pernah berbeda.
     *
     * @return array<string, array{title: string, intro: string, eyebrow: string}>
     */
    private function sectionPages(): array
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
     * Daftar sebelas bagian situs untuk pusat informasi di beranda.
     * Galeri memakai rute sendiri, sedangkan sisanya melewati rute
     * tangkap-lambung /{section}.
     *
     * @return array<int, array{key: string, title: string, intro: string, eyebrow: string, href: string, count_label: string|null}>
     */
    private function exploreSections(array $site): array
    {
        $pages = $this->sectionPages();
        $pages['galeri'] = [
            'title' => 'Galeri kegiatan',
            'intro' => 'Dokumentasi kegiatan belajar, praktik, dan karya siswa SMK INFOKOM.',
            'eyebrow' => 'Dokumentasi',
        ];

        // Jumlah dinamis dihitung dari koleksi yang sudah dimuat halaman
        // beranda (tanpa kueri tambahan per bagian).
        $totals = [
            'akademik' => [$site['programs']->count(), 'program keahlian'],
            'kesiswaan' => [$site['gal']->count(), 'kegiatan siswa'],
            'prestasi' => [$site['prest']->count(), 'prestasi siswa'],
            'industri' => [$site['partners']->count(), 'mitra industri'],
            'jepang' => [$site['jsteps']->count(), 'tahap magang'],
            'informasi' => [$site['news']->count(), 'artikel'],
            'galeri' => [$site['gal']->count(), 'foto kegiatan'],
            'alumni' => [$site['alum']->count(), 'cerita alumni'],
        ];

        $order = [
            'profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang',
            'ppdb', 'informasi', 'galeri', 'alumni', 'kontak',
        ];

        return collect($order)->map(function (string $key) use ($pages, $totals) {
            [$count, $unit] = $totals[$key] ?? [null, null];

            return [
                'key' => $key,
                'title' => $pages[$key]['title'],
                'intro' => $pages[$key]['intro'],
                'eyebrow' => $pages[$key]['eyebrow'],
                'href' => $key === 'galeri' ? route('galeri.index') : route('site.section', $key),
                'count_label' => $count ? $count.' '.$unit : null,
            ];
        })->all();
    }

    private function siteData(): array
    {
        $site = [
            'programs' => Program::orderBy('id')->get()->map(fn ($p) => [
                'short' => $p->short, 'slug' => $p->slug, 'icon' => $p->icon, 'name' => $p->name,
                'desc' => $p->description, 'skills' => $p->skills,
                'jobs' => $p->jobs, 'fac' => $p->facilities,
            ]),
            'news' => Post::with('category')->orderByDesc('published_at')->get()->map(fn ($n) => [
                'cat' => $n->category?->name ?? 'Berita', 't' => $n->title, 'x' => $n->excerpt,
                'd' => $n->published_at->locale('id')->translatedFormat('j F Y'),
                'a' => $n->art, 'slug' => $n->slug,
            ]),
            'agenda' => Agenda::orderBy('date')->get()->map(fn ($a) => [
                'd' => $a->date->format('d'),
                'm' => $a->date->locale('id')->translatedFormat('M'),
                't' => $a->title, 'w' => $a->place_time,
            ]),
            'prest' => Achievement::orderBy('id')->get()->map(fn ($a) => [
                't' => $a->title, 'who' => $a->who, 'lv' => $a->level, 'y' => $a->year,
            ]),
            'gal' => GalleryItem::with('category')->orderBy('id')->get()->map(fn ($g) => [
                'c' => $g->category?->name ?? 'Galeri', 't' => $g->title, 's' => $g->size, 'a' => $g->art,
            ]),
            'jsteps' => JapanStep::orderBy('id')->get()->map(fn ($s) => [
                't' => $s->title, 'd' => $s->description, 'l' => $s->duration,
            ]),
            'partners' => Partner::orderBy('id')->get()->map(fn ($p) => [
                'n' => $p->name, 'c' => $p->category,
            ]),
            'alum' => Alumnus::orderBy('id')->get()->map(fn ($a) => [
                'n' => $a->name, 'r' => $a->role_desc, 'q' => $a->quote,
            ]),
            'tracer' => TracerStat::orderBy('id')->get()->map(fn ($t) => [
                'l' => $t->label, 'v' => $t->value, 'c' => $t->color,
            ]),
            'announce' => Announcement::orderBy('id')->pluck('text'),
        ];

        return $site;
    }

    private function contactData(): array
    {
        $contact = SchoolSettings::contact();

        $address = collect($contact['address'] ?? [])
            ->filter(fn ($value) => filled($value));
        $fullAddress = $address->values()->implode(', ');

        $mapsQuery = trim(($contact['name'] ?? 'SMK INFOKOM').($fullAddress ? ', '.$fullAddress : ''));
        $mapsUrl = $contact['maps_url'] ?: 'https://www.google.com/maps/search/?'.http_build_query(['api' => '1', 'query' => $mapsQuery ?: 'SMK INFOKOM']);
        $mapsEmbedUrl = 'https://www.google.com/maps?q='.rawurlencode($mapsQuery ?: ($contact['name'] ?? 'SMK INFOKOM')).'&output=embed';

        return [
            ...$contact,
            'address_lines' => $address,
            'full_address' => $fullAddress,
            'maps_url' => $mapsUrl,
            'maps_embed_url' => $mapsEmbedUrl,
            'whatsapp_url' => fn (?string $number, string $message) => WhatsApp::link($number, $message),
        ];
    }
}
