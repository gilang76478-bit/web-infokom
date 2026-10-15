<?php
namespace App\Http\Controllers;
use App\Models\{Program, Post, Agenda, Achievement, GalleryItem, JapanStep, Partner, Alumnus, TracerStat, Announcement, Teacher};
use App\Support\SchoolSettings;

class SiteController extends Controller
{
    public function home()
    {
        $site = $this->siteData();

        return view('pages.home', compact('site'));
    }

    public function section(string $section)
    {
        $pages = [
            'profil' => ['title' => 'Profil sekolah', 'intro' => 'Mengenal SMK INFOKOM lebih dekat: lingkungan belajar, visi, misi, dan nilai yang membentuk lulusan siap berkarya.', 'eyebrow' => 'Tentang sekolah'],
            'akademik' => ['title' => 'Program keahlian', 'intro' => 'Pilih jalur belajar yang sesuai minat dan bangun keterampilan melalui praktik, proyek, dan pengalaman industri.', 'eyebrow' => 'Akademik'],
            'kesiswaan' => ['title' => 'Kesiswaan', 'intro' => 'Kegiatan, ruang berkembang, dan pencapaian siswa di dalam maupun di luar kelas.', 'eyebrow' => 'Kehidupan siswa'],
            'prestasi' => ['title' => 'Prestasi siswa', 'intro' => 'Apresiasi untuk kerja keras dan karya siswa di berbagai tingkat kompetisi.', 'eyebrow' => 'Karya dan pencapaian'],
            'industri' => ['title' => 'Mitra dunia industri', 'intro' => 'Kolaborasi yang membuka pengalaman praktik, wawasan profesi, dan kesiapan kerja.', 'eyebrow' => 'Kolaborasi'],
            'jepang' => ['title' => 'Program Magang Jepang', 'intro' => 'Kenali tahapan persiapan, seleksi, dan pembekalan sebelum mengikuti program magang.', 'eyebrow' => 'Peluang internasional'],
            'ppdb' => ['title' => 'Penerimaan murid baru', 'intro' => 'Informasi terbaru mengenai pendaftaran, jadwal, dan tahapan seleksi calon murid.', 'eyebrow' => 'PPDB / SPMB'],
            'informasi' => ['title' => 'Informasi sekolah', 'intro' => 'Berita, pengumuman, dan agenda terbaru dari SMK INFOKOM.', 'eyebrow' => 'Kabar terkini'],
            'galeri' => ['title' => 'Galeri kegiatan', 'intro' => 'Cuplikan aktivitas belajar, berkarya, dan bertumbuh bersama di SMK INFOKOM.', 'eyebrow' => 'Dokumentasi'],
            'alumni' => ['title' => 'Alumni dan tracer study', 'intro' => 'Cerita perjalanan alumni dan gambaran aktivitas setelah menyelesaikan pendidikan.', 'eyebrow' => 'Jejak lulusan'],
            'kontak' => ['title' => 'Hubungi sekolah', 'intro' => 'Pilih jalur komunikasi yang sesuai. Kami siap membantu pertanyaan tentang sekolah, PPDB, administrasi, dan kerja sama.', 'eyebrow' => 'Kontak SMK INFOKOM'],
        ];

        abort_unless(isset($pages[$section]), 404);

        $data = match ($section) {
            'profil' => [
                ...SchoolSettings::profile(),
                'teachers' => Teacher::query()->with('program')->ordered()->get(),
            ],
            'akademik' => Program::query()->with('teachers')->orderBy('short')->get(),
            'kesiswaan' => [
                'achievements' => Achievement::orderByDesc('year')->get(),
                'activities' => GalleryItem::orderBy('id')->get(),
            ],
            'prestasi' => Achievement::orderByDesc('year')->get(),
            'industri' => Partner::orderBy('name')->get(),
            'jepang' => JapanStep::orderBy('id')->get(),
            'ppdb' => [
                'posts' => Post::where('category', 'PPDB')->orderByDesc('published_at')->get(),
                'programs' => Program::orderBy('short')->get(),
            ],
            'informasi' => [
                'posts' => Post::orderByDesc('published_at')->get(),
                'agendas' => Agenda::orderBy('date')->get(),
            ],
            'galeri' => GalleryItem::orderBy('id')->get(),
            'alumni' => [
                'alumni' => Alumnus::orderBy('id')->get(),
                'tracer' => TracerStat::orderBy('id')->get(),
            ],
            'kontak' => $this->contactData(),
            default => [],
        };

        return view('sections.show', compact('section', 'pages', 'data'));
    }

    private function siteData(): array
    {
        $site = [
            'programs' => Program::orderBy('id')->get()->map(fn ($p) => [
                'short' => $p->short, 'slug' => $p->slug, 'icon' => $p->icon, 'name' => $p->name,
                'desc' => $p->description, 'skills' => $p->skills,
                'jobs' => $p->jobs, 'fac' => $p->facilities,
            ]),
            'news' => Post::orderByDesc('published_at')->get()->map(fn ($n) => [
                'cat' => $n->category, 't' => $n->title, 'x' => $n->excerpt,
                'd' => $n->published_at->locale('id')->translatedFormat('j F Y'),
                'a' => $n->art,
            ]),
            'agenda' => Agenda::orderBy('date')->get()->map(fn ($a) => [
                'd' => $a->date->format('d'),
                'm' => $a->date->locale('id')->translatedFormat('M'),
                't' => $a->title, 'w' => $a->place_time,
            ]),
            'prest' => Achievement::orderBy('id')->get()->map(fn ($a) => [
                't' => $a->title, 'who' => $a->who, 'lv' => $a->level, 'y' => $a->year,
            ]),
            'gal' => GalleryItem::orderBy('id')->get()->map(fn ($g) => [
                'c' => $g->category, 't' => $g->title, 's' => $g->size, 'a' => $g->art,
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

        $mapsQuery = trim(($contact['name'] ?? 'SMK INFOKOM') . ($fullAddress ? ', ' . $fullAddress : ''));
        $mapsUrl = $contact['maps_url'] ?: 'https://www.google.com/maps/search/?' . http_build_query(['api' => '1', 'query' => $mapsQuery ?: 'SMK INFOKOM']);
        $mapsEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($mapsQuery ?: ($contact['name'] ?? 'SMK INFOKOM')) . '&output=embed';

        return [
            ...$contact,
            'address_lines' => $address,
            'full_address' => $fullAddress,
            'maps_url' => $mapsUrl,
            'maps_embed_url' => $mapsEmbedUrl,
            'whatsapp_url' => fn (?string $number, string $message) => filled($number)
                ? 'https://wa.me/' . preg_replace('/\D+/', '', $number) . '?' . http_build_query(['text' => $message])
                : null,
        ];
    }
}