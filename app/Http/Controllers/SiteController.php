<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Agenda;
use App\Models\Alumnus;
use App\Models\Announcement;
use App\Models\Facility;
use App\Models\GalleryItem;
use App\Models\JapanReel;
use App\Models\JapanStep;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\TracerStat;
use App\Support\SchoolSettings;
use App\Support\SiteSections;
use App\Support\WhatsApp;

class SiteController extends Controller
{
    /**
     * Berkas sitemap.xml: beranda, sebelas bagian, indeks berita, halaman
     * program keahlian, dan artikel berita terbit. Hanya halaman publik
     * GET yang disetor — halaman admin dan formulir tidak ikut.
     */
    public function sitemap()
    {
        $entries = [
            ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ];

        foreach (SiteSections::all() as $item) {
            $entries[] = ['loc' => $item['href'], 'changefreq' => 'monthly', 'priority' => '0.8'];
        }

        $entries[] = ['loc' => route('berita.index'), 'changefreq' => 'weekly', 'priority' => '0.7'];

        $entries[] = ['loc' => route('guru.index'), 'changefreq' => 'monthly', 'priority' => '0.7'];

        $entries[] = ['loc' => route('bkk.index'), 'changefreq' => 'weekly', 'priority' => '0.7'];

        // Formulir PPDB siap cetak untuk pendaftaran langsung ke sekolah.
        $entries[] = ['loc' => route('ppdb.printable'), 'changefreq' => 'monthly', 'priority' => '0.8'];

        foreach (Program::query()->orderBy('short')->get() as $program) {
            $entries[] = [
                'loc' => route('program.show', $program),
                'lastmod' => $program->updated_at->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        foreach (Post::query()->published()->with('category')->orderByDesc('published_at')->get() as $post) {
            $entries[] = [
                'loc' => route('berita.show', $post),
                'lastmod' => ($post->published_at ?? $post->updated_at)->toDateString(),
                'changefreq' => 'yearly',
                'priority' => '0.6',
            ];
        }

        return response()->view('sitemap', compact('entries'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt dinamis: mengizinkan seluruh penjelajah dan menunjuk ke
     * sitemap.xml dengan domain yang benar untuk lingkungan berjalan.
     */
    public function robots()
    {
        return response(
            "User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

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
                // Fasilitas kini modul konten tersedia dengan foto HD;
                // dikelola lewat panel admin (menu Fasilitas sekolah).
                'facilities' => Facility::ordered()->get(),
            ],
            'akademik' => Program::query()->with('teachers')->orderBy('short')->get(),
            'kesiswaan' => [
                'achievements' => Achievement::orderByDesc('year')->get(),
                'activities' => GalleryItem::with('category')->orderBy('id')->get(),
            ],
            'prestasi' => Achievement::orderByDesc('year')->get(),
            'industri' => Partner::orderBy('name')->get(),
            'jepang' => [
                // Tahapan program (dengan foto opsional per langkah) dan
                // dokumentasi Reel Instagram — keduanya dikelola panel admin.
                'steps' => JapanStep::orderBy('id')->get(),
                'reels' => JapanReel::ordered()->get(),
            ],
            'ppdb' => [
                'posts' => Post::published()->with('category')
                    ->whereHas('category', fn ($query) => $query->where('slug', 'ppdb'))
                    ->orderByDesc('published_at')
                    ->get(),
                'programs' => Program::orderBy('short')->get(),
                // Status penerimaan, persyaratan, alur, dan FAQ dari
                // pengaturan sekolah; kontak panitia dari pengaturan kontak.
                'spmb' => SchoolSettings::ppdb(),
                'contact' => SchoolSettings::contact(),
            ],
            'informasi' => [
                'posts' => Post::published()->with('category')->orderByDesc('published_at')->get(),
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
     * Kini didelegasikan ke SiteSections agar teks halaman seksi, pusat
     * informasi beranda, sitemap, dan structured data selalu sama.
     *
     * @return array<string, array{title: string, intro: string, eyebrow: string}>
     */
    private function sectionPages(): array
    {
        return SiteSections::pages();
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

        return collect(SiteSections::all())
            ->map(function (array $item, string $key) use ($totals) {
                [$count, $unit] = $totals[$key] ?? [null, null];

                return [
                    'key' => $key,
                    'title' => $item['title'],
                    'intro' => $item['intro'],
                    'eyebrow' => $item['eyebrow'],
                    'href' => $item['href'],
                    'count_label' => $count ? $count.' '.$unit : null,
                ];
            })
            ->values()
            ->all();
    }

    private function siteData(): array
    {
        // Foto bagian "Profil" beranda dari Pengaturan Profil (path disk
        // publik atau URL eksternal) — null bila admin belum mengisi/mengosongkan.
        $homePhoto = (string) (SchoolSettings::profile()['home_photo'] ?? '');
        $homePhotoUrl = filled($homePhoto)
            ? (preg_match('#^(https?:)?//#i', $homePhoto) ? $homePhoto : asset('storage/'.ltrim($homePhoto, '/')))
            : null;

        $site = [
            'home_photo' => $homePhotoUrl,
            'programs' => Program::orderBy('id')->get()->map(fn ($p) => [
                'short' => $p->short, 'slug' => $p->slug, 'icon' => $p->icon, 'name' => $p->name,
                'desc' => $p->description, 'skills' => $p->skills,
                'jobs' => $p->jobs, 'fac' => $p->facilities,
            ]),
            'news' => Post::published()->with('category')->orderByDesc('published_at')->get()->map(fn ($n) => [
                'cat' => $n->category?->name ?? 'Berita', 't' => $n->title, 'x' => $n->excerpt,
                'd' => $n->published_at->locale('id')->translatedFormat('j F Y'),
                'a' => $n->art, 'i' => $n->image_url, 'slug' => $n->slug,
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
                'u' => $g->url,
            ]),
            'jsteps' => JapanStep::orderBy('id')->get()->map(fn ($s) => [
                't' => $s->title, 'd' => $s->description, 'l' => $s->duration,
            ]),
            'partners' => Partner::orderBy('id')->get()->map(fn ($p) => [
                'n' => $p->name, 'c' => $p->category, 'logo' => $p->logo_url,
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
