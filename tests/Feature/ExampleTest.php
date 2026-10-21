<?php

namespace Tests\Feature;

use App\Filament\Pages\SchoolProfileSettings;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Achievement;
use App\Models\Agenda;
use App\Models\Alumnus;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Facility;
use App\Models\GalleryItem;
use App\Models\JapanReel;
use App\Models\JapanStep;
use App\Models\Partner;
use App\Models\Post;
use App\Models\PpdbApplicant;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\Teacher;
use App\Models\TracerStat;
use App\Models\User;
use App\Models\Vacancy;
use App\Support\SchoolSettings;
use Database\Seeders\SchoolSettingsSeeder;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Pilih jalur, bangun keahlian')
            ->assertSee('program keahlian')
            ->assertDontSee('01 / PROFIL')
            ->assertDontSee('02 / AKADEMIK');
    }

    public function test_home_is_the_information_hub_for_all_eleven_sections(): void
    {
        $this->seed();

        $response = $this->get(route('home'))->assertOk();

        // Sebelas bagian tampil sebagai kartu pusat informasi; sepuluh bagian
        // melewati rute /{section} dan galeri memakai rute sendiri.
        foreach (['profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang', 'ppdb', 'informasi', 'alumni', 'kontak'] as $section) {
            $response->assertSee(route('site.section', $section), false);
        }
        $response->assertSee(route('galeri.index'), false);

        // Judul tiap kartu memakai metadata yang sama dengan halaman sektinya.
        foreach (['Profil sekolah', 'Program keahlian', 'Kesiswaan', 'Prestasi siswa', 'Mitra dunia industri', 'Program Magang Jepang', 'Penerimaan murid baru', 'Informasi sekolah', 'Galeri kegiatan', 'Alumni dan tracer study', 'Hubungi sekolah'] as $title) {
            $response->assertSee($title);
        }

        // Jumlah dinamis dihitung dari data sekolah, bukan angka tetap.
        $response
            ->assertSee(Program::count().' program keahlian')
            ->assertSee(GalleryItem::count().' kegiatan siswa')
            ->assertSee(Achievement::count().' prestasi siswa')
            ->assertSee(Partner::count().' mitra industri')
            ->assertSee(JapanStep::count().' tahap magang')
            ->assertSee(Post::count().' artikel')
            ->assertSee(Alumnus::count().' cerita alumni');

        // Tanpa konten sama sekali, pusat informasi tetap tampil utuh.
        Post::query()->delete();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('site.section', 'kesiswaan'), false)
            ->assertSee(route('site.section', 'kontak'), false);
    }

    public function test_public_navigation_pages_are_available(): void
    {
        foreach (['profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang', 'ppdb', 'informasi', 'alumni', 'kontak'] as $section) {
            $this->get(route('site.section', $section))->assertOk();
        }

        // Galeri kini mempunyai rute sendiri dengan filter kategori.
        $this->get(route('galeri.index'))->assertOk();
    }

    public function test_navigation_parent_links_open_each_section_from_the_top(): void
    {
        $this->seed();

        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<nav class="menu"[^>]*>(.*?)<\/nav>/s', $html, $menu);
        $this->assertNotEmpty($menu[1] ?? null, 'Menu utama tidak ditemukan pada beranda.');

        // Label induk (Profil sebagai tautan langsung, plus tautan induk tiap
        // dropdown) harus membuka halaman dari bagian teratas tanpa potongan anchor.
        foreach (['profil', 'akademik', 'kesiswaan', 'informasi'] as $section) {
            $this->assertMatchesRegularExpression(
                '/<a[^>]*href="'.preg_quote(route('site.section', $section), '/').'"[^>]*>/',
                $menu[1],
                "Label {$section} pada menu tidak menautkan bagian teratas halamannya.",
            );
        }
    }

    public function test_every_menu_link_targets_a_real_page_and_anchor(): void
    {
        $this->seed();

        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<nav class="menu"[^>]*>(.*?)<\/nav>/s', $html, $menu);
        preg_match_all('/<a[^>]+href="([^"]+)"/', $menu[1] ?? '', $links);

        $checked = 0;
        foreach (array_unique($links[1]) as $href) {
            if (! str($href)->startsWith(url('/'))) {
                continue; // Tautan eksternal di luar cakupan uji navigasi.
            }

            $parts = explode('#', $href, 2);
            $path = parse_url($parts[0], PHP_URL_PATH) ?: '/';
            $response = $this->get($path);
            $this->assertSame(200, $response->getStatusCode(), "Tautan menu menuju halaman yang tidak ada: {$href}");

            if (isset($parts[1]) && $parts[1] !== '') {
                $response->assertSee('id="'.$parts[1].'"', false);
            }

            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'Tidak ada tautan internal pada menu yang teruji.');
    }

    public function test_profile_page_renders_defaults_when_settings_are_empty(): void
    {
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Selamat Datang di SMK INFOKOM')
            ->assertSee('Menjadi sekolah kejuruan yang unggul')
            ->assertSee('Lengkapi melalui pengaturan sekolah')

            // Fasilitas bukan lagi bagian dari pengaturan teks: tanpa
            // data di modul Fasilitas, seksi ini menampilkan arahan kosong.
            ->assertSee('Fasilitas Pendukung Pembelajaran')
            ->assertSee('Daftar dan foto fasilitas sekolah belum dipublikasikan');
    }

    public function test_profile_page_shows_values_saved_in_settings(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'profile_vision'],
            ['value' => 'Visi hasil pengaturan admin'],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'profile_npsn'],
            ['value' => '20998877'],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'profile_mission'],
            ['value' => "Misi pertama dari admin\nMisi kedua dari admin"],
        );
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Visi hasil pengaturan admin')
            ->assertDontSee('Menjadi sekolah kejuruan yang unggul')
            ->assertSee('20998877')
            ->assertSee('Misi pertama dari admin')
            ->assertSee('Misi kedua dari admin');
    }

    public function test_profile_page_renders_history_entries_and_principal_from_settings(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'profile_history'],
            ['value' => "2005|SMK INFOKOM didirikan|Berdiri pada 10 Mei 2005.\n2023|Akreditasi A|Peringkat akreditasi A."],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'profile_principal_name'],
            ['value' => 'Ir. Hj. Liliek Rahmaningsih, M.M.Pd.'],
        );

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('SMK INFOKOM didirikan')
            ->assertSee('Berdiri pada 10 Mei 2005.')
            ->assertSee('Akreditasi A')
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.M.Pd.')
            ->assertDontSee('Riwayat sekolah sedang dilengkapi.')
            ->assertDontSee('Syaiful');
    }

    public function test_profile_page_keeps_history_placeholder_and_never_shows_unverified_principal(): void
    {
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Data resmi')
            ->assertSee('Riwayat sekolah sedang dilengkapi.')
            ->assertSee('Belum dikonfirmasi')
            ->assertDontSee('Syaiful');
    }

    public function test_profile_hero_shows_quick_facts_and_complete_quick_navigation(): void
    {
        $html = $this->get(route('site.section', 'profil'))->assertOk()->getContent();

        // Seluruh jangkar navigasi cepat menunjuk ke bagian yang benar-benar ada.
        foreach (['sambutan', 'tentang', 'identitas', 'visi-misi', 'sejarah', 'struktur-organisasi', 'guru', 'fasilitas', 'keunggulan'] as $anchor) {
            $this->assertStringContainsString('href="#'.$anchor.'"', $html);
            $this->assertStringContainsString('id="'.$anchor.'"', $html);
        }

        // Hero hanya menampilkan identitas yang sudah terisi (status bawaan
        // "Swasta" tampil; NPSN, tahun berdiri, dan akreditasi belum).
        $this->assertMatchesRegularExpression('/<dl class="profile-hero-facts">(.*?)<\/dl>/s', $html);
        preg_match('/<dl class="profile-hero-facts">(.*?)<\/dl>/s', $html, $facts);
        $this->assertStringContainsString('Swasta', $facts[1]);
        $this->assertStringNotContainsString('NPSN', $facts[1]);
        $this->assertStringNotContainsString('Tahun Berdiri', $facts[1]);

        // Setelah admin mengisi pengaturan, fakta tersebut ikut tampil.
        SiteSetting::updateOrCreate(['key' => 'profile_npsn'], ['value' => '20998877']);
        SiteSetting::updateOrCreate(['key' => 'profile_accreditation'], ['value' => 'A (Unggul)']);

        $html = $this->get(route('site.section', 'profil'))->assertOk()->getContent();
        preg_match('/<dl class="profile-hero-facts">(.*?)<\/dl>/s', $html, $facts);
        $this->assertStringContainsString('20998877', $facts[1]);
        $this->assertStringContainsString('A (Unggul)', $facts[1]);
        $this->assertStringNotContainsString('Tahun Berdiri', $facts[1]);
    }

    public function test_school_settings_seeder_fills_identity_and_contact_pages(): void
    {
        $this->seed(SchoolSettingsSeeder::class);

        $this->assertDatabaseHas('site_settings', ['key' => 'profile_npsn', 'value' => '20238531']);

        $this->get(route('site.section', 'kontak'))
            ->assertOk()
            ->assertSee('Jl. Letjen Ibrahim Adjie No. 178')
            ->assertSee('(0251) 8348108')
            ->assertSee('smk.infokom@gmail.com');

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('20238531')
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.M.Pd.')
            ->assertSee('SMK INFOKOM Bogor didirikan')
            ->assertSee('SK 421.5/86-Disdik Tahun 2005');
    }

    public function test_school_settings_seeder_never_overwrites_existing_settings(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'school_email_general'],
            ['value' => 'admin@sekolah.test'],
        );

        $this->seed(SchoolSettingsSeeder::class);

        $this->assertDatabaseHas('site_settings', ['key' => 'school_email_general', 'value' => 'admin@sekolah.test']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_npsn', 'value' => '20238531']);
    }

    public function test_admin_can_save_profile_settings_from_filament_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(SchoolProfileSettings::class)
            ->set('data.vision', 'Visi versi uji admin')
            ->set('data.mission', "Misi uji satu\nMisi uji dua")
            ->set('data.npsn', '12345678')
            ->set('data.history', '2005|Berdiri|Didirikan pada 10 Mei 2005.')
            ->call('save');

        $this->assertDatabaseHas('site_settings', ['key' => 'profile_vision', 'value' => 'Visi versi uji admin']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_mission', 'value' => "Misi uji satu\nMisi uji dua"]);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_npsn', 'value' => '12345678']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_history', 'value' => '2005|Berdiri|Didirikan pada 10 Mei 2005.']);

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Visi versi uji admin')
            ->assertSee('12345678')
            ->assertSee('Didirikan pada 10 Mei 2005.');
    }

    public function test_every_admin_route_requires_admin_privileges(): void
    {
        $message = ContactMessage::create([
            'name' => 'Pengunjung',
            'email' => 'visitor@example.test',
            'whatsapp' => '081234567890',
            'subject' => 'Umum',
            'message' => 'Pesan untuk pengujian proteksi rute admin.',
        ]);
        $applicant = PpdbApplicant::create([
            'registration_number' => 'INF-2026-AUDIT1',
            'full_name' => 'Calon Murid',
            'phone' => '081234567890',
            'origin_school' => 'SMP Negeri 1',
            'program' => 'RPL',
        ]);

        $routes = [
            ['GET', 'admin'],
            ['GET', 'admin/posts'],
            ['GET', 'admin/categories'],
            ['GET', 'admin/gallery-items'],
            ['GET', 'admin/settings'],
            ['PUT', 'admin/settings'],
            ['GET', 'admin/ppdb'],
            ['GET', 'admin/pesan'],
            ['GET', 'admin/content/agenda'],
            ['GET', 'admin/content/agenda/create'],
            ['POST', 'admin/content/agenda'],
            ['GET', 'admin/content/agenda/1/edit'],
            ['PUT', 'admin/content/agenda/1'],
            ['DELETE', 'admin/content/agenda/1'],
            ['PATCH', 'admin/pesan/'.$message->id.'/read'],
            ['PATCH', 'admin/ppdb/'.$applicant->id],
        ];

        // Pengunjung tamu: semua rute admin diarahkan ke halaman login.
        foreach ($routes as [$method, $uri]) {
            $response = $this->call($method, $uri);
            $this->assertSame(302, $response->getStatusCode(), "Tamu diterima di {$method} {$uri}");
        }

        // User login yang bukan admin: dilarang masuk (403).
        $this->actingAs(User::factory()->create());
        foreach ($routes as [$method, $uri]) {
            $response = $this->call($method, $uri);
            $this->assertSame(403, $response->getStatusCode(), "Non-admin diterima di {$method} {$uri}");
        }
    }

    public function test_contact_form_returns_indonesian_validation_messages(): void
    {
        $this->from(route('site.section', 'kontak'))
            ->post(route('contact-messages.store'), [
                'name' => '',
                'email' => 'bukan-email',
                'whatsapp' => '123',
                'subject' => 'Tidak Ada',
                'message' => 'pendek',
            ])
            ->assertRedirect(route('site.section', 'kontak'))
            ->assertSessionHasErrors(['name', 'email', 'whatsapp', 'subject', 'message']);

        $errors = session('errors');
        $this->assertSame('Nama wajib diisi.', $errors->first('name'));
        $this->assertSame('Format email tidak valid.', $errors->first('email'));
        $this->assertSame(
            'Nomor WhatsApp hanya boleh berisi angka, spasi, dan tanda + ( ) - dengan panjang 9-30 karakter.',
            $errors->first('whatsapp'),
        );
        $this->assertSame('Keperluan yang dipilih tidak valid.', $errors->first('subject'));
        $this->assertSame('Pesan minimal 10 karakter.', $errors->first('message'));
    }

    public function test_admin_can_update_school_settings(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.settings.update'), ['school_name' => 'SMK INFOKOM Bogor'])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('site_settings', ['key' => 'school_name', 'value' => 'SMK INFOKOM Bogor']);
        $this->get(route('home'))->assertSee('SMK INFOKOM Bogor');

        // Halaman pengaturan sekolah tetap dapat dibuka (tanpa membuat tabel di runtime).
        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan website');
    }

    public function test_custom_error_pages_are_used_instead_of_framework_defaults(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertStatus(404)
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke beranda', false);

        // Halaman 500 hanya dipakai saat APP_DEBUG=false (mode produksi).
        config(['app.debug' => false]);
        $response = app(Handler::class)->render(
            Request::create('/', 'GET'),
            new \RuntimeException('Kegagalan tak terduga'),
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Terjadi kesalahan di server kami', $response->getContent());
    }

    public function test_contact_page_does_not_show_unverified_sample_contacts(): void
    {
        $this->get(route('site.section', 'kontak'))
            ->assertOk()
            ->assertSee('Jam operasional')
            ->assertSee('Kirim pesan')
            ->assertSee('Nomor telepon, WhatsApp, dan email resmi belum dikonfigurasi.')
            ->assertDontSee('Jl. Pendidikan No. 1')
            ->assertDontSee('000-0000');
    }

    public function test_configured_contact_details_create_a_prefilled_whatsapp_link(): void
    {
        config()->set('school.contact.address.Alamat lengkap', 'Jl. Contoh Nomor 10');
        config()->set('school.contact.address.Kota/Kabupaten', 'Bogor');
        config()->set('school.contact.whatsapp.general', '+62 (812) 3456-7890');
        config()->set('school.contact.emails.general', 'kontak@example.test');

        $this->get(route('site.section', 'kontak'))
            ->assertOk()
            ->assertSee('Jl. Contoh Nomor 10')
            ->assertSee('Buka di Google Maps')
            ->assertSee('Chat via WhatsApp')
            ->assertSee('wa.me/6281234567890')
            ->assertSee('kontak@example.test');
    }

    public function test_local_whatsapp_number_is_converted_to_international_link(): void
    {
        config()->set('school.contact.whatsapp.general', '0878-7307-1400');

        $this->get(route('site.section', 'kontak'))
            ->assertOk()
            ->assertSee('0878-7307-1400')
            ->assertSee('wa.me/6287873071400')
            ->assertDontSee('wa.me/087873071400');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('wa.me/6287873071400');
    }

    public function test_contact_form_saves_a_message_for_the_admin_inbox(): void
    {
        $response = $this->post(route('contact-messages.store'), [
            'name' => 'Orang Tua Murid',
            'email' => 'orangtua@example.test',
            'whatsapp' => '081234567890',
            'subject' => 'PPDB',
            'message' => 'Mohon informasi jadwal penerimaan murid baru.',
        ]);

        $response->assertRedirect()->assertSessionHas('contact_message_sent', true);
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Orang Tua Murid',
            'subject' => 'PPDB',
            'is_read' => false,
        ]);
    }

    public function test_admin_can_mark_contact_message_as_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $message = ContactMessage::create([
            'name' => 'Pengunjung',
            'email' => 'visitor@example.test',
            'whatsapp' => '081234567890',
            'subject' => 'Umum',
            'message' => 'Saya ingin bertanya tentang kunjungan sekolah.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Pengunjung')
            // Nomor format lokal pada pesan pengunjung tetap menghasilkan tautan wa.me yang valid.
            ->assertSee('wa.me/6281234567890');
        $this->actingAs($admin)
            ->patch(route('admin.messages.read', $message))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'is_read' => true]);
    }

    public function test_filament_panel_requires_an_admin_account(): void
    {
        $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_filament_panel_and_sign_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get(route('filament.admin.auth.login'))->assertOk();
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
        $this->post(route('filament.admin.auth.logout'))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_admin_can_write_publish_and_edit_news_in_filament(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('filament.admin.resources.posts.index'))->assertOk();
        $this->get(route('filament.admin.resources.categories.index'))->assertOk();

        $category = Category::firstOrCreate(['name' => 'PPDB', 'type' => 'berita']);

        // Menulis dan menerbitkan artikel baru (slug dibuat otomatis).
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Artikel Uji Coba Tahap 4',
                'category_id' => $category->id,
                'published_at' => '2026-10-10',
                'slug' => '',
                'art' => 2,
                'excerpt' => 'Ringkasan artikel uji coba.',
                'body' => "Paragraf pertama artikel uji.\nParagraf kedua artikel uji.",
            ])
            ->call('create');

        $post = Post::where('title', 'Artikel Uji Coba Tahap 4')->firstOrFail();
        $this->assertSame('artikel-uji-coba-tahap-4', $post->slug);
        $this->assertSame($category->id, $post->category_id);

        // Hasil publikasi tampil di daftar berita dan isi lengkapnya di detail.
        $this->get(route('berita.index'))->assertOk()->assertSee('Artikel Uji Coba Tahap 4');
        $this->get(route('berita.show', $post))
            ->assertOk()
            ->assertSee('Paragraf pertama artikel uji.')
            ->assertSee('Paragraf kedua artikel uji.');

        // Menyunting artikel: judul berubah, slug tetap stabil.
        Livewire::test(EditPost::class, ['record' => $post->getKey()])
            ->fillForm(['title' => 'Artikel Uji Coba Tahap 4 Disunting'])
            ->call('save');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Artikel Uji Coba Tahap 4 Disunting']);
        $this->assertSame('artikel-uji-coba-tahap-4', $post->fresh()->slug);
    }

    public function test_security_headers_and_asset_cache_rules_are_sent(): void
    {
        // Header keamanan menempel pada setiap respons framework.
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Halaman tidak lagi memuat font eksternal (self-hosted via build).
        $this->get(route('home'))
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertDontSee('fonts.gstatic.com', false);

        // Cadangan cache aset hanya untuk respons sukses.
        $handler = app(SecurityHeaders::class);

        $build = $handler->handle(
            Request::create('/build/assets/site-abc123.css', 'GET'),
            fn () => new Response('css', 200),
        );
        // Symfony menormalkan urutan token Cache-Control; verifikasi tiap direktif.
        $buildCache = (string) $build->headers->get('Cache-Control');
        $this->assertStringContainsString('immutable', $buildCache);
        $this->assertStringContainsString('max-age=31536000', $buildCache);
        $this->assertStringContainsString('public', $buildCache);

        $missing = $handler->handle(
            Request::create('/build/assets/hilang.css', 'GET'),
            fn () => new Response('tidak ada', 404),
        );
        $this->assertStringNotContainsString('immutable', (string) $missing->headers->get('Cache-Control'));

        $storage = $handler->handle(
            Request::create('/storage/gallery/foto.jpg', 'GET'),
            fn () => new Response('gambar', 200),
        );
        $storageCache = (string) $storage->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=604800', $storageCache);
        $this->assertStringContainsString('public', $storageCache);
    }

    public function test_admin_can_manage_categories_in_filament_with_auto_slug(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateCategory::class)
            ->fillForm(['name' => 'Kegiatan Ekstrakurikuler', 'type' => 'berita'])
            ->call('create');

        $category = Category::where('name', 'Kegiatan Ekstrakurikuler')->firstOrFail();

        $this->assertSame('kegiatan-ekstrakurikuler', $category->slug);
        $this->assertSame('berita', $category->type);
    }

    public function test_news_list_page_filters_by_category(): void
    {
        $this->seed();

        // Seeder kini mengisi kategori berita nyata; test menambah artikelnya sendiri.
        $ppdb = Category::firstOrCreate(['name' => 'PPDB', 'type' => 'berita']);
        $kegiatan = Category::firstOrCreate(['name' => 'Kegiatan', 'type' => 'berita']);

        Post::create([
            'category_id' => $ppdb->id,
            'title' => 'Jadwal penerimaan murid baru tahun ajaran 2027',
            'excerpt' => 'Ringkasan jadwal penerimaan murid baru.',
            'body' => 'Isi artikel jadwal penerimaan murid baru.',
            'published_at' => '2026-01-05',
            'art' => 1,
        ]);
        Post::create([
            'category_id' => $kegiatan->id,
            'title' => 'Praktik kerja siswa di mitra industri',
            'excerpt' => 'Ringkasan kegiatan praktik siswa.',
            'body' => 'Isi artikel kegiatan praktik siswa.',
            'published_at' => '2026-01-03',
            'art' => 2,
        ]);

        $listing = $this->get(route('berita.index'));
        $listing
            ->assertOk()
            ->assertSee('Berita & Artikel')
            ->assertSee('Jadwal penerimaan murid baru tahun ajaran 2027')
            ->assertSee('Praktik kerja siswa di mitra industri');

        // Tautan tiap kartu mengarah ke halaman detail per slug.
        foreach (Post::query()->pluck('slug') as $slug) {
            $listing->assertSee('/berita/'.$slug, false);
        }

        // Filter kategori hanya menampilkan artikel kategori tersebut.
        $this->get(route('berita.index', ['kategori' => 'ppdb']))
            ->assertOk()
            ->assertSee('Jadwal penerimaan murid baru tahun ajaran 2027')
            ->assertDontSee('Praktik kerja siswa di mitra industri');

        // Kategori tidak dikenal → 404 (halaman error kustom).
        $this->get(route('berita.index', ['kategori' => 'kategori-tidak-ada']))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_news_list_shows_an_empty_state_when_nothing_is_published(): void
    {
        $this->seed();

        // Seeder kini mengisi berita nyata; kosongkan dulu untuk menguji empty state.
        Post::query()->delete();

        $this->get(route('berita.index'))
            ->assertOk()
            ->assertSee('Belum ada berita yang dipublikasikan pada kategori ini.');
    }

    public function test_news_list_paginates_and_keeps_the_category_filter(): void
    {
        $this->seed();
        $category = Category::firstOrCreate(['name' => 'Kegiatan', 'type' => 'berita']);

        // 12 artikel dalam satu kategori → melewati batas 9 artikel per halaman.
        foreach (range(1, 12) as $index) {
            Post::create([
                'category_id' => $category->id,
                'title' => 'Artikel Paginasi '.$index,
                'excerpt' => 'Ringkasan artikel paginasi '.$index.'.',
                'body' => 'Isi artikel paginasi '.$index.'.',
                'published_at' => '2026-01-01',
                'art' => 1,
            ]);
        }

        $url = route('berita.index', ['kategori' => $category->slug]);

        // Halaman pertama menautkan halaman berikutnya sambil membawa filter kategori.
        $this->get($url)
            ->assertOk()
            ->assertSee('Halaman 1 dari')
            ->assertSee('kategori='.$category->slug.'&amp;page=2', false);

        // Halaman kedua tetap dapat dibuka dan berisi artikel lanjutan.
        $this->get($url.'&page=2')
            ->assertOk()
            ->assertSee('Artikel Paginasi');
    }

    public function test_home_page_queries_do_not_grow_with_more_posts(): void
    {
        $this->seed();
        $category = Category::firstOrCreate(['name' => 'Kegiatan', 'type' => 'berita']);

        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $baseline = count(DB::getQueryLog());
        DB::flushQueryLog();

        foreach (range(1, 6) as $index) {
            Post::create([
                'category_id' => $category->id,
                'title' => 'Artikel Tambahan '.$index,
                'excerpt' => 'Ringkasan tambahan '.$index.'.',
                'body' => 'Isi tambahan '.$index.'.',
                'published_at' => '2026-01-01',
                'art' => 0,
            ]);
        }

        // Buang kueri pembuatan artikel agar yang dihitung hanya render halaman.
        DB::flushQueryLog();
        $this->get('/')->assertOk();
        $withMorePosts = count(DB::getQueryLog());
        DB::flushQueryLog();

        // Enam artikel tambahan tidak boleh menambah enam kueri kategori (N+1).
        $this->assertLessThanOrEqual(2, $withMorePosts - $baseline);
    }

    public function test_news_detail_page_shows_the_full_article(): void
    {
        $this->seed();

        $category = Category::firstOrCreate(['name' => 'Akademik', 'type' => 'berita']);
        $post = Post::create([
            'category_id' => $category->id,
            'title' => 'Persiapan ujian kompetensi keahlian',
            'excerpt' => 'Ringkasan persiapan ujian kompetensi keahlian.',
            'body' => "Paragraf pertama persiapan ujian.\nPersyaratan lengkap dan jadwal diumumkan sekolah.",
            'published_at' => '2026-01-06',
            'art' => 3,
        ]);

        $this->get(route('berita.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->excerpt)
            ->assertSee('Paragraf pertama persiapan ujian.')
            ->assertSee('Persyaratan lengkap');

        $this->assertNotNull($post->category);
        $this->assertSame('Akademik', $post->category->name);

        $this->get('/berita/artikel-yang-tidak-ada')->assertNotFound();
    }

    public function test_seeded_site_publishes_only_verified_school_content(): void
    {
        $this->seed();

        // Konten contoh sudah diganti: berita, prestasi, alumni, mitra, dan
        // pengumuman semuanya punya sumber resmi sekolah. Agenda dan tracer
        // study sengaja dikosongkan karena tidak ada data resmi yang terbit.
        $this->assertSame(6, Post::count());
        $this->assertSame(1, Achievement::count());
        $this->assertSame(0, Agenda::count());
        $this->assertSame(0, TracerStat::count());
        // Urutan nama diseragamkan di PHP (case-insensitive) agar tidak
        // bergantung kolasi basis data yang dipakai lingkungan uji.
        $partnerNames = Partner::all()->pluck('name')
            ->sort(fn (string $a, string $b) => strcasecmp($a, $b) ?: strcmp($a, $b))
            ->values()
            ->all();
        $this->assertSame(
            [
                'bank bjb', 'Bank Mandiri', 'Bank Syariah Indonesia',
                'BONET IT Solution Partner', 'by.U', 'Citraweb Solusi Teknologi',
                'IPB University', 'Jawa Pos', 'Mega Motor', 'MSV Studio',
                'Politeknik Negeri Jakarta', 'PT. Guna Senaputra Sejahtera',
                'Raja Promo Advertising', 'Universitas Djuanda',
                'Universitas Ibn Khaldun', 'Universitas Pakuan',
            ],
            $partnerNames,
        );
        // Empat belas mitra membawa logo HD; dua mitra lama dibiarkan tanpa
        // logo sampai sekolah mengunggahnya sendiri lewat panel admin.
        $this->assertSame(14, Partner::whereNotNull('logo')->count());
        $this->assertSame(
            ['M. Fahmi Ramadhan', 'Nazib Sopian', 'Surya Difraja'],
            Alumnus::orderBy('name')->pluck('name')->all(),
        );

        // Halaman publik menampilkan konten nyata tersebut; agenda & tracer
        // yang belum punya data resmi memakai empty state.
        $this->get(route('site.section', 'informasi'))
            ->assertOk()
            ->assertSee('SPMB Tahun Ajaran 2026-2027 dibuka')
            ->assertSee('Alumni TKJ diterima bekerja di PT. Mitra Copierindo Mandiri')
            ->assertSee('Belum ada agenda mendatang.');

        $this->get(route('site.section', 'prestasi'))
            ->assertOk()
            ->assertSee('Alisa Putri Hasanah')
            ->assertSee('Lomba Poster Pertolongan Pertama pada Luka Psikologis')
            ->assertDontSee('Belum ada pencapaian yang dipublikasikan.');

        $this->get(route('site.section', 'alumni'))
            ->assertOk()
            ->assertSee('Nazib Sopian')
            ->assertSee('Surya Difraja')
            ->assertSee('Data tracer study lulusan akan dipublikasikan')
            ->assertDontSee('“”');

        $this->get(route('site.section', 'industri'))
            ->assertOk()
            ->assertSee('PT. Guna Senaputra Sejahtera')
            ->assertSee('Raja Promo Advertising')
            ->assertDontSee('Cakrawala Teknologi');

        $this->get(route('home'))
            ->assertOk()
            // Beranda menampilkan enam mitra teratas (kini logo-logo HD).
            ->assertSee('Bank Mandiri')
            ->assertSee('Alumni TKJ diterima bekerja di PT. Mitra Copierindo Mandiri')
            ->assertSee('Alisa Putri Hasanah')
            ->assertDontSee('“”');

        // Tanpa konten sama sekali, halaman publik tetap tampil memakai empty state.
        Post::query()->delete();
        Achievement::query()->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belum ada kabar terbaru')
            ->assertSee('Pencapaian siswa akan diumumkan')
            ->assertDontSee('“”');

        $this->get(route('site.section', 'prestasi'))
            ->assertOk()
            ->assertSee('Belum ada pencapaian yang dipublikasikan.');
    }

    public function test_gallery_page_shows_photos_with_lazy_loading_and_category_filter(): void
    {
        Storage::fake('public');
        $this->seed();

        // Seluruh foto galeri benih kini berisi foto kegiatan, tetapi
        // fallback ilustrasi wajib tetap bekerja: satu item sengaja dibuat
        // tanpa foto, dan satu foto "diunggah" tampil dengan lazy loading.
        Storage::disk('public')->put('gallery/foto-uji.jpg', 'bytes-gambar-uji');
        GalleryItem::where('title', 'Praktik jaringan di Lab TKJ')->firstOrFail()
            ->update(['image_path' => 'gallery/foto-uji.jpg']);
        GalleryItem::create([
            'category_id' => Category::ofType('galeri')->where('slug', 'praktik')->value('id'),
            'title' => 'Tanpa Foto Uji',
            'size' => 'n',
            'art' => 4,
        ]);

        $listing = $this->get(route('galeri.index'));
        $listing
            ->assertOk()
            ->assertSee('Galeri Kegiatan')
            ->assertSee(asset('storage/gallery/foto-uji.jpg'), false)
            ->assertSee('loading="lazy"', false);

        // Item tanpa foto memakai fallback ilustrasi (kolom art).
        $listing->assertSee('galeri-art-', false);
        $listing->assertSee('Tanpa Foto Uji');

        // Filter kategori galeri.
        $this->get(route('galeri.index', ['kategori' => 'praktik']))
            ->assertOk()
            ->assertSee('Praktik jaringan di Lab TKJ')
            ->assertDontSee('Upacara Hari Kemerdekaan');

        // Kategori tidak dikenal → 404 (halaman error kustom).
        $this->get(route('galeri.index', ['kategori' => 'tidak-ada']))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_gallery_page_paginates_when_photos_grow(): void
    {
        $this->seed();
        $category = Category::ofType('galeri')->firstOrFail();

        // 15 foto tambahan → total melewati batas 12 foto per halaman.
        foreach (range(1, 15) as $index) {
            GalleryItem::create([
                'category_id' => $category->id,
                'title' => 'Foto Paginasi '.$index,
                'size' => 'n',
                'art' => 1,
            ]);
        }

        $this->get(route('galeri.index'))
            ->assertOk()
            ->assertSee('Halaman 1 dari')
            ->assertSee('page=2', false);

        $this->get(route('galeri.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Foto Paginasi');
    }

    public function test_admin_can_upload_a_gallery_photo_in_filament(): void
    {
        Storage::fake('public');
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $category = Category::ofType('galeri')->firstOrFail();
        $file = UploadedFile::fake()->image('kegiatan-lab.jpg', 800, 600);

        $this->get(route('filament.admin.resources.gallery-items.index'))->assertOk();

        Livewire::test(CreateGalleryItem::class)
            ->fillForm([
                'title' => 'Foto Unggahan Uji',
                'category_id' => $category->id,
                'size' => 'w',
                'art' => 3,
                'image_path' => [$file],
            ])
            ->call('create');

        $item = GalleryItem::where('title', 'Foto Unggahan Uji')->firstOrFail();

        $this->assertNotNull($item->image_path);
        $this->assertSame($category->id, $item->category_id);
        Storage::disk('public')->assertExists($item->image_path);

        // Foto hasil unggahan tampil di halaman galeri publik.
        $this->get(route('galeri.index'))
            ->assertOk()
            ->assertSee('Foto Unggahan Uji')
            ->assertSee(asset('storage/'.$item->image_path), false);

        // File ikut terhapus ketika record dihapus (tidak ada file yatim).
        $path = $item->image_path;
        $item->delete();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_ppdb_form_stores_an_application_and_returns_a_number(): void
    {
        $this->seed();
        $program = Program::firstOrFail();

        $response = $this->post(route('ppdb.apply'), [
            'full_name' => 'Calon Murid',
            'phone' => '081234567890',
            'origin_school' => 'SMP Negeri 1',
            'program' => $program->short,
            'consent' => '1',
        ]);

        $response->assertRedirect()->assertSessionHas('registration_number');
        $this->assertDatabaseHas('ppdb_applicants', [
            'full_name' => 'Calon Murid',
            'program' => $program->short,
            'status' => 'Menunggu Verifikasi',
        ]);
        $this->assertMatchesRegularExpression('/^INF-2026-[A-Z0-9]{6}$/', PpdbApplicant::firstOrFail()->registration_number);
    }

    public function test_spmb_page_shows_status_flow_requirements_and_faq(): void
    {
        $this->seed();

        // Bawaan sistem: status, periode, alur, dan FAQ tampil; persyaratan
        // masih kosong sampai admin mengisi daftar resmi panitia.
        $this->get(route('site.section', 'ppdb'))
            ->assertOk()
            ->assertSee('Formulir pendaftaran daring aktif')
            ->assertSee('Tahun Ajaran 2026/2027')
            ->assertSee('Alur pendaftaran')
            ->assertSee('Simpan nomor pendaftaran')
            ->assertSee('Pertanyaan umum pendaftaran')
            ->assertSee('Daftar persyaratan dokumen diumumkan panitia')
            ->assertSee('Kontak pendaftaran')
            ->assertSee('WhatsApp sekolah')
            ->assertSee('Kontak khusus panitia PPDB belum diatur')
            ->assertSee(route('ppdb.apply'));

        // Pengaturan buatan admin menggantikan bawaan dan memunculkan
        // JSON-LD FAQPage untuk mesin pencari.
        SiteSetting::updateOrCreate(['key' => 'ppdb_status'], ['value' => 'Pendaftaran gelombang pertama dibuka']);
        SiteSetting::updateOrCreate(['key' => 'ppdb_requirements'], ['value' => "Akta kelahiran\nSurat keterangan lulus"]);
        SiteSetting::updateOrCreate(['key' => 'ppdb_faq'], ['value' => 'Boleh mendaftar langsung ke sekolah?|Silakan hubungi panitia PPDB untuk konfirmasi.']);

        $html = $this->get(route('site.section', 'ppdb'))
            ->assertOk()
            ->assertSee('Pendaftaran gelombang pertama dibuka')
            ->assertSee('Akta kelahiran')
            ->assertSee('Surat keterangan lulus')
            ->assertSee('Boleh mendaftar langsung ke sekolah?')
            ->assertSee('FAQPage', false)
            ->getContent();

        $this->assertStringNotContainsString('Daftar persyaratan dokumen diumumkan panitia', $html);
    }

    public function test_admin_can_update_an_applicant_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $applicant = PpdbApplicant::create([
            'registration_number' => 'INF-2026-ABC123',
            'full_name' => 'Calon Murid',
            'phone' => '081234567890',
            'origin_school' => 'SMP Negeri 1',
            'program' => 'RPL',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.ppdb.update', $applicant), ['status' => 'Terverifikasi'])
            ->assertRedirect();

        $this->assertDatabaseHas('ppdb_applicants', [
            'id' => $applicant->id,
            'status' => 'Terverifikasi',
        ]);
    }

    public function test_admin_can_manage_tracer_statistics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.content.create', 'tracer'))->assertOk();
        $this->post(route('admin.content.store', 'tracer'), [
            'label' => 'Bekerja',
            'value' => 72,
            'color' => '#1A2B3C',
        ])->assertRedirect(route('admin.content.index', 'tracer'));

        $this->assertDatabaseHas('tracer_stats', ['label' => 'Bekerja', 'value' => 72]);
    }

    public function test_admin_content_modules_open_their_lists_and_forms(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        foreach (['akademik', 'guru', 'agenda', 'prestasi', 'jepang', 'reel-jepang', 'industri', 'fasilitas', 'lowongan', 'alumni', 'tracer', 'pengumuman'] as $type) {
            $this->get(route('admin.content.index', $type))->assertOk();
            $this->get(route('admin.content.create', $type))->assertOk();
        }
    }

    public function test_program_slugs_are_generated_automatically_and_stay_stable(): void
    {
        $program = Program::create([
            'short' => 'TEST',
            'icon' => 'code',
            'name' => 'Program Uji Coba Teknologi',
            'description' => 'Deskripsi program uji coba.',
            'skills' => ['Keahlian uji'],
            'jobs' => ['Pekerjaan uji'],
            'facilities' => ['Fasilitas uji'],
        ]);

        $this->assertSame('program-uji-coba-teknologi', $program->slug);

        // Nama berubah tidak memutus tautan yang sudah dibagikan.
        $program->update(['name' => 'Nama Baru Program Uji']);
        $this->assertSame('program-uji-coba-teknologi', $program->fresh()->slug);

        // Nama sama menghasilkan slug unik, bukan tabrakan.
        $duplicate = Program::create([
            'short' => 'TST2',
            'icon' => 'code',
            'name' => 'Program Uji Coba Teknologi',
            'description' => 'Deskripsi program uji kedua.',
            'skills' => ['Keahlian uji'],
            'jobs' => ['Pekerjaan uji'],
            'facilities' => ['Fasilitas uji'],
        ]);

        $this->assertSame('program-uji-coba-teknologi-2', $duplicate->slug);
    }

    public function test_program_detail_page_shows_full_information_by_slug(): void
    {
        $this->seed();
        $program = Program::firstOrFail();

        $this->get(route('program.show', $program))
            ->assertOk()
            ->assertSee($program->name)
            ->assertSee($program->description)
            ->assertSee($program->skills[0])
            ->assertSee($program->jobs[0])
            ->assertSee($program->facilities[0]);

        $this->get('/program/slug-yang-tidak-ada')->assertNotFound();
        $this->get('/program')->assertNotFound();
    }

    public function test_program_list_and_home_link_to_the_detail_pages(): void
    {
        $this->seed();
        $program = Program::firstOrFail();
        $url = route('program.show', $program);

        $this->get(route('site.section', 'akademik'))
            ->assertOk()
            ->assertSee($url, false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($url, false);
    }

    public function test_only_the_four_real_programs_are_published(): void
    {
        $this->seed();

        $this->assertSame(['DKV', 'PSPT', 'RPL', 'TKJ'], Program::orderBy('short')->pluck('short')->all());
        $this->assertSame(
            ['desain-komunikasi-visual', 'produksi-dan-siaran-program-televisi', 'rekayasa-perangkat-lunak', 'teknik-komputer-dan-jaringan'],
            Program::orderBy('short')->pluck('slug')->all(),
        );

        $akademik = $this->get(route('site.section', 'akademik'))->assertOk();

        foreach (Program::orderBy('short')->get() as $program) {
            $akademik->assertSee($program->name);
            $this->get(route('program.show', $program))->assertOk()->assertSee($program->name);
        }

        // Program keahlian yang tidak dimiliki sekolah tidak ikut terbit.
        $this->assertDatabaseMissing('programs', ['short' => 'PPLG']);
        $this->assertDatabaseMissing('programs', ['short' => 'AKL']);
        $this->assertDatabaseMissing('programs', ['short' => 'MPLB']);
        $akademik->assertDontSee('Akuntansi');

        // Formulir PPDB hanya menerima program keahlian yang benar-benar ada.
        $this->get(route('site.section', 'ppdb'))
            ->assertOk()
            ->assertSee('Produksi dan Siaran Program Televisi');

        $this->post(route('ppdb.apply'), [
            'full_name' => 'Calon Murid',
            'phone' => '081234567890',
            'origin_school' => 'SMP Negeri 1',
            'program' => 'PPLG',
            'consent' => '1',
        ])->assertSessionHasErrors('program');
    }

    public function test_real_programs_stay_editable_through_the_admin_module(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $program = Program::where('short', 'PSPT')->firstOrFail();

        $this->put(route('admin.content.update', ['akademik', $program->id]), [
            'short' => $program->short,
            'icon' => $program->icon,
            'name' => $program->name,
            'description' => $program->description,
            'skills' => implode("\n", $program->skills),
            'jobs' => implode("\n", $program->jobs),
            'facilities' => implode("\n", $program->facilities),
        ])->assertRedirect(route('admin.content.index', 'akademik'));

        // Ikon program baru lolos validasi modul admin dan isinya tidak berubah.
        $this->assertDatabaseHas('programs', ['id' => $program->id, 'short' => 'PSPT', 'icon' => 'brief']);
        $this->assertSame($program->skills, $program->fresh()->skills);
    }

    public function test_profile_page_never_shows_unverified_teacher_rows(): void
    {
        $this->seed();

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.M.Pd.')
            ->assertDontSee('Syaiful')
            ->assertDontSee('Rina Wulandari')
            ->assertDontSee('Ahmad Hidayat');

        // Seluruh guru & staf dari daftar resmi sekolah tampil: 32 nama,
        // semuanya berfoto, dan kepala sekolah tetap satu orang.
        $this->assertSame(32, Teacher::count());
        $this->assertSame(32, Teacher::whereNotNull('photo')->count());
        $this->assertSame(1, Teacher::where('position', 'Kepala Sekolah')->count());
    }

    public function test_teachers_are_shown_on_profile_and_program_detail_with_relations(): void
    {
        $this->seed();

        // Fixture mandiri: test ini tidak lagi bergantung pada data guru contoh.
        $program = Program::firstOrFail();
        $teacher = Teacher::create([
            'program_id' => $program->id,
            'name' => 'Guru Uji Program Keahlian',
            'nip' => '199001012020031001',
            'position' => 'Guru Produktif',
            'sort_order' => 12,
        ]);

        // Relasi Eloquent dua arah: belongsTo dan hasMany.
        $this->assertNotNull($teacher->program);
        $this->assertTrue($teacher->program->teachers->contains($teacher));
        $this->assertSame($program->id, $teacher->program_id);

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Guru & Tenaga Kependidikan')
            ->assertSee($teacher->name)
            ->assertSee($teacher->nip)
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.M.Pd.')
            ->assertDontSee('Syaiful');

        $this->get(route('program.show', $program))
            ->assertOk()
            ->assertSee($teacher->name);
    }

    public function test_admin_can_manage_teachers(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $program = Program::firstOrFail();

        $this->get(route('admin.content.index', 'guru'))
            ->assertOk()
            ->assertSee('Guru & Tenaga Kependidikan');
        $this->get(route('admin.content.create', 'guru'))->assertOk();

        $this->post(route('admin.content.store', 'guru'), [
            'name' => 'Guru Baru Produksi',
            'nip' => '199001012020031001',
            'position' => 'Guru Produktif',
            'program_id' => $program->id,
            'photo' => '',
            'sort_order' => 50,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $teacher = Teacher::where('name', 'Guru Baru Produksi')->firstOrFail();
        $this->assertSame($program->id, $teacher->program_id);
        $this->assertNull($teacher->photo);

        // Tabel daftar admin menampilkan nama program, bukan id mentah.
        $this->get(route('admin.content.index', 'guru'))
            ->assertOk()
            ->assertSee($program->name);

        $this->put(route('admin.content.update', ['guru', $teacher->id]), [
            'name' => 'Guru Produksi Diperbarui',
            'nip' => '199001012020031001',
            'position' => 'Ketua Program Keahlian',
            'program_id' => $program->id,
            'photo' => '',
            'sort_order' => 51,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => 'Guru Produksi Diperbarui',
            'position' => 'Ketua Program Keahlian',
        ]);
    }

    public function test_admin_can_upload_a_teacher_photo_and_public_pages_show_it(): void
    {
        Storage::fake('public');
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $program = Program::firstOrFail();

        $this->post(route('admin.content.store', 'guru'), [
            'name' => 'Guru Berfoto',
            'nip' => '',
            'position' => 'Guru Produktif',
            'program_id' => $program->id,
            'photo' => UploadedFile::fake()->image('guru.jpg', 400, 400),
            'sort_order' => 60,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $teacher = Teacher::where('name', 'Guru Berfoto')->firstOrFail();
        $this->assertNotNull($teacher->photo);
        $this->assertStringStartsWith('photos/', $teacher->photo);
        Storage::disk('public')->assertExists($teacher->photo);

        // Halaman publik memakai URL storage, bukan path mentah maupun placeholder inisial.
        $photoUrl = asset('storage/'.ltrim($teacher->photo, '/'));
        $this->get(route('guru.index'))
            ->assertOk()
            ->assertSee($photoUrl, false);
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee($photoUrl, false);
        $this->get(route('program.show', $program))
            ->assertOk()
            ->assertSee($photoUrl, false);
    }

    public function test_admin_can_replace_or_remove_a_teacher_photo_without_orphan_files(): void
    {
        Storage::fake('public');
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $program = Program::firstOrFail();

        $this->post(route('admin.content.store', 'guru'), [
            'name' => 'Guru Foto Dua',
            'position' => 'Guru Produktif',
            'program_id' => $program->id,
            'photo' => UploadedFile::fake()->image('pertama.jpg', 300, 300),
            'sort_order' => 61,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $teacher = Teacher::where('name', 'Guru Foto Dua')->firstOrFail();
        $firstPhoto = $teacher->photo;
        Storage::disk('public')->assertExists($firstPhoto);

        // Unggah baru: foto lama ikut terhapus (tidak ada berkas yatim).
        $this->put(route('admin.content.update', ['guru', $teacher->id]), [
            'name' => $teacher->name,
            'position' => $teacher->position,
            'program_id' => $program->id,
            'photo' => UploadedFile::fake()->image('kedua.jpg', 300, 300),
            'sort_order' => $teacher->sort_order,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $teacher->refresh();
        $this->assertNotSame($firstPhoto, $teacher->photo);
        Storage::disk('public')->assertMissing($firstPhoto);
        Storage::disk('public')->assertExists($teacher->photo);

        // Simpan tanpa berkas baru: foto lama tetap (bukan ditimpa null).
        $secondPhoto = $teacher->photo;
        $this->put(route('admin.content.update', ['guru', $teacher->id]), [
            'name' => 'Guru Foto Dua Dipangkas',
            'position' => $teacher->position,
            'program_id' => $program->id,
            'sort_order' => $teacher->sort_order,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $teacher->refresh();
        $this->assertSame('Guru Foto Dua Dipangkas', $teacher->name);
        $this->assertSame($secondPhoto, $teacher->photo);
        Storage::disk('public')->assertExists($secondPhoto);

        // Centang "Hapus foto": kolom jadi kosong dan berkas ikut terhapus.
        $this->put(route('admin.content.update', ['guru', $teacher->id]), [
            'name' => $teacher->name,
            'position' => $teacher->position,
            'program_id' => $program->id,
            'photo_remove' => '1',
            'sort_order' => $teacher->sort_order,
        ])->assertRedirect(route('admin.content.index', 'guru'));

        $this->assertNull($teacher->refresh()->photo);
        Storage::disk('public')->assertMissing($secondPhoto);

        // Hapus catutan guru: berkas foto terakhir ikut dibersihkan.
        $this->delete(route('admin.content.destroy', ['guru', $teacher->id]))
            ->assertRedirect(route('admin.content.index', 'guru'));
        Storage::disk('public')->assertMissing($secondPhoto);
    }

    public function test_facilities_show_hd_photos_on_profile_and_are_manageable_in_admin(): void
    {
        Storage::fake('public');
        $this->seed();

        // Seeder: 13 fasilitas berfoto dari data sekolah, tampil berurutan.
        $this->assertSame(13, Facility::count());
        $this->assertSame(13, Facility::whereNotNull('photo_path')->count());

        $html = $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Fasilitas Pendukung Pembelajaran')
            ->assertSee('Laboratorium PSPT')
            ->assertDontSee('Daftar dan foto fasilitas sekolah belum dipublikasikan')
            ->getContent();

        // Setiap kartu memakai foto storage nyata, bukan placeholder teks.
        preg_match_all('#<img[^>]+src="([^"]*storage/photos/facilities/[^"]+)"#', $html, $matches);
        $this->assertCount(13, $matches[1], 'Foto fasilitas tidak tampil lengkap di /profil.');

        // Admin dapat menambah fasilitas baru berfoto lewat panel.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post(route('admin.content.store', 'fasilitas'), [
            'title' => 'Aula Serbaguna',
            'description' => 'Ruang kegiatan bersama siswa dan guru.',
            'photo_path' => UploadedFile::fake()->image('aula.jpg', 900, 675),
            'sort_order' => 20,
        ])->assertRedirect(route('admin.content.index', 'fasilitas'));

        $facility = Facility::where('title', 'Aula Serbaguna')->firstOrFail();
        $this->assertStringStartsWith('photos/', $facility->photo_path);
        Storage::disk('public')->assertExists($facility->photo_path);

        $photoUrl = asset('storage/'.ltrim($facility->photo_path, '/'));
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Aula Serbaguna')
            ->assertSee($photoUrl, false);

        // Hapus catutan: berkas foto ikut dibersihkan (tidak ada yatim).
        $this->delete(route('admin.content.destroy', ['fasilitas', $facility->id]))
            ->assertRedirect(route('admin.content.index', 'fasilitas'));
        Storage::disk('public')->assertMissing($facility->photo_path);
    }

    public function test_bkk_page_shows_posters_category_news_and_is_listed_in_navigation(): void
    {
        Storage::fake('public');
        $this->seed();

        $html = $this->get(route('bkk.index'))
            ->assertOk()
            ->assertSee('Bursa Kerja Khusus (BKK)')
            ->assertSee(asset('images/bkk-logo.jpg'), false)
            // Poster lowongan bawaan seeder tampil sebagai gambar storage.
            ->assertSee(asset('storage/photos/vacancies/poster-bursa-lowongan.jpg'), false)
            // Berita penempatan alumni lewat kategori BKK ikut tampil.
            ->assertSee('Alumni TKJ diterima bekerja di PT. Mitra Copierindo Mandiri')
            ->assertSee(route('berita.show', 'alumni-tkj-diterima-bekerja-di-pt-mitra-copierindo-mandiri'), false)
            ->getContent();

        // Remah navigasi tiga tingkat dan sitemap memuat halaman BKK.
        $breadcrumb = $this->firstJsonLd($html, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb, 'Halaman BKK tanpa remah navigasi.');
        $this->assertSame(route('bkk.index'), $breadcrumb['itemListElement'][2]['item']);
        $this->get(route('sitemap'))->assertOk()->assertSee(route('bkk.index'));

        // Menu Akademik menunjuk halaman BKK.
        $this->get(route('site.section', 'akademik'))
            ->assertOk()
            ->assertSee('href="'.route('bkk.index').'"', false);

        // Admin dapat menambah poster lowongan baru lewat panel.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post(route('admin.content.store', 'lowongan'), [
            'title' => 'Lowongan Operator Produksi',
            'description' => 'Dibuka untuk lulusan TKJ dan RPL.',
            'image_path' => UploadedFile::fake()->image('poster-lowongan.jpg', 700, 1000),
            'link' => 'https://example.test/lowongan',
            'posted_at' => '2026-10-20',
        ])->assertRedirect(route('admin.content.index', 'lowongan'));

        $vacancy = Vacancy::where('title', 'Lowongan Operator Produksi')->firstOrFail();
        Storage::disk('public')->assertExists($vacancy->image_path);

        $this->get(route('bkk.index'))
            ->assertOk()
            ->assertSee('Lowongan Operator Produksi')
            ->assertSee(asset('storage/'.ltrim($vacancy->image_path, '/')), false)
            ->assertSee('https://example.test/lowongan', false);

        // Hapus catutan: berkas poster ikut dibersihkan.
        $this->delete(route('admin.content.destroy', ['lowongan', $vacancy->id]))
            ->assertRedirect(route('admin.content.index', 'lowongan'));
        Storage::disk('public')->assertMissing($vacancy->image_path);
    }

    public function test_japan_page_shows_step_photos_and_manageable_instagram_reels(): void
    {
        Storage::fake('public');
        $this->seed();

        // Tiga Reel Instagram resmi dari contoh ikut tampil, lengkap dengan
        // tautan menuju akun sekolah.
        $this->assertSame(3, JapanReel::count());

        $this->get(route('site.section', 'jepang'))
            ->assertOk()
            ->assertSee('Pendaftaran')
            ->assertSee('Dokumentasi Program Jepang')
            ->assertSee('Informasi Magang Jepang')
            ->assertSee('https://www.instagram.com/smk.infokom_official/reel/DZE77u4oNN_/', false)
            ->assertSee('@smk.infokom_official');

        // Admin mengunggah foto pada sebuah langkah tahapan lewat panel.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $step = JapanStep::where('title', 'Pelatihan')->firstOrFail();
        $this->put(route('admin.content.update', ['jepang', $step->id]), [
            'title' => $step->title,
            'description' => $step->description,
            'duration' => $step->duration,
            'photo_path' => UploadedFile::fake()->image('pelatihan.jpg', 800, 500),
        ])->assertRedirect(route('admin.content.index', 'jepang'));

        $step->refresh();
        $this->assertNotNull($step->photo_path);
        Storage::disk('public')->assertExists($step->photo_path);
        $this->get(route('site.section', 'jepang'))
            ->assertOk()
            ->assertSee(asset('storage/'.ltrim($step->photo_path, '/')), false);

        // Admin menambah Reel baru berposter lewat panel.
        $this->post(route('admin.content.store', 'reel-jepang'), [
            'url' => 'https://www.instagram.com/smk.infokom_official/reel/ABC123xyZ_-/',
            'title' => 'Reel Uji Coba Kegiatan',
            'description' => 'Reel dokumentasi kegiatan sekolah.',
            'poster_path' => UploadedFile::fake()->image('poster-reel.jpg', 720, 900),
            'sort_order' => 4,
        ])->assertRedirect(route('admin.content.index', 'reel-jepang'));

        $reel = JapanReel::where('title', 'Reel Uji Coba Kegiatan')->firstOrFail();
        Storage::disk('public')->assertExists($reel->poster_path);

        $this->get(route('site.section', 'jepang'))
            ->assertOk()
            ->assertSee('Reel Uji Coba Kegiatan')
            ->assertSee(asset('storage/'.ltrim($reel->poster_path, '/')), false);

        // Hapus catutan reel: poster ikut dibersihkan.
        $this->delete(route('admin.content.destroy', ['reel-jepang', $reel->id]))
            ->assertRedirect(route('admin.content.index', 'reel-jepang'));
        Storage::disk('public')->assertMissing($reel->poster_path);
    }

    public function test_principal_portrait_uses_the_principal_teacher_photo(): void
    {
        Storage::fake('public');
        $this->seed();

        $principal = Teacher::where('position', 'Kepala Sekolah')->firstOrFail();
        $path = UploadedFile::fake()->image('kepala-sekolah.jpg', 300, 400)->store('photos/teachers', 'public');
        $principal->update(['photo' => $path]);

        $photoUrl = asset('storage/'.ltrim($path, '/'));
        $html = $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee($photoUrl, false)
            ->assertDontSee('Foto resmi akan ditampilkan setelah ditambahkan.')
            ->getContent();

        // Foto kepala sekolah tampil di potret sambutan, kartu struktur, dan daftar guru.
        $this->assertSame(3, substr_count($html, $photoUrl), 'Foto kepala sekolah tidak tampil lengkap di potret, struktur, dan daftar guru.');

        // Tanpa foto, placeholder inisial kembali tampil (jujur, bukan foto karangan).
        $principal->update(['photo' => null]);
        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Foto resmi akan ditampilkan setelah ditambahkan.')
            ->assertDontSee($photoUrl, false);
    }

    public function test_ppdb_printable_form_renders_for_offline_registration(): void
    {
        $this->seed();

        $html = $this->get(route('ppdb.printable'))
            ->assertOk()
            ->assertSee('FORMULIR PENDAFTARAN CALON MURID BARU')
            ->assertSee('Data Calon Murid')
            ->assertSee('Pilihan Program Keahlian')
            ->assertSee('Data Orang Tua / Wali')
            ->assertSee('Kelengkapan Dokumen')
            ->assertSee('Pernyataan')
            ->assertSee('data-print-form')
            ->assertSee('Unduh formulir (PDF)')
            ->assertSee(route('ppdb.printable.pdf'), false)
            ->assertSee('Isi formulir daring')
            ->assertSee('Tahun Ajaran 2026/2027')
            ->assertSee(route('ppdb.apply'), false)
            ->getContent();

        // Kop surat memuat identitas sekolah, dan remah navigasi terpasang.
        $this->assertStringContainsString('SMK INFOKOM', $html);
        $breadcrumb = $this->firstJsonLd($html, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb, 'Halaman formulir cetak tidak memiliki remah navigasi Schema.org.');

        // Program keahlian ikut tercantum sebagai pilihan.
        foreach (Program::orderBy('short')->get() as $program) {
            $this->assertStringContainsString($program->short, $html);
        }

        // Halaman PPDB menautkan formulir cetak, dan sitemap ikut menyertakannya.
        $this->get(route('site.section', 'ppdb'))
            ->assertOk()
            ->assertSee(route('ppdb.printable'), false)
            ->assertSee('Daftar langsung di sekolah');

        $sitemap = $this->get(route('sitemap'))->assertOk()->getContent();
        $this->assertStringContainsString(route('ppdb.printable'), $sitemap);
    }

    public function test_ppdb_printable_form_downloads_as_a_pdf_file(): void
    {
        $this->seed();

        $response = $this->get(route('ppdb.printable.pdf'));

        // Berkas PDF sah: tipe konten, nama berkas unduhan, penanda %PDF,
        // dan berkas nyata (bukan halaman kosong) — ukurannya besar karena
        // font ikut ter-embed di dalam PDF.
        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=formulir-ppdb-smk-infokom.pdf');

        $body = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $body, 'Respons bukan berkas PDF yang sah.');
        $this->assertGreaterThan(50_000, strlen($body), 'PDF terlalu kecil; kemungkinan gagal merender formulir.');

        // Teks di dalam PDF disubset oleh dompdf sehingga tidak dicari sebagai
        // string mentah; kebenaran isinya diverifikasi lewat template yang
        // sama yang dilempar ke dompdf.
        $html = view('ppdb.printable-pdf', [
            'contact' => \App\Support\SchoolSettings::contact(),
            'spmb' => \App\Support\SchoolSettings::ppdb(),
            'programs' => Program::orderBy('short')->get(),
        ])->render();

        $this->assertStringContainsString('FORMULIR PENDAFTARAN CALON MURID BARU', $html);
        $this->assertStringContainsString('Data Calon Murid', $html);
        $this->assertStringContainsString('Kelengkapan Dokumen', $html);
        $this->assertStringContainsString('Pernyataan', $html);
        foreach (Program::orderBy('short')->get() as $program) {
            $this->assertStringContainsString($program->short, $html);
        }
    }

    public function test_sitemap_lists_home_sections_and_detail_pages_as_valid_xml(): void
    {
        $this->seed();

        $response = $this->get(route('sitemap'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $body = $response->getContent();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $body);

        // Berkas XML harus sah (terurai tanpa galat) dan berabsen namespace sitemap.
        $xml = simplexml_load_string($body);
        $this->assertNotFalse($xml, 'Berkas sitemap.xml bukan XML yang sah.');
        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $xml->getNamespaces(true)[''] ?? null);

        // Iterasi eksplisit: SimpleXML multi-unsur tidak boleh dirangkum
        // lewat collect() karena kuncinya bisa saling menimpa.
        $locations = [];
        foreach ($xml->url as $url) {
            $locations[] = (string) $url->loc;
        }

        // Beranda, sebelas bagian, dan indeks berita ikut disetor.
        $expected = [
            route('home'),
            route('site.section', 'profil'),
            route('site.section', 'akademik'),
            route('site.section', 'kesiswaan'),
            route('site.section', 'prestasi'),
            route('site.section', 'industri'),
            route('site.section', 'jepang'),
            route('site.section', 'ppdb'),
            route('site.section', 'informasi'),
            route('site.section', 'alumni'),
            route('site.section', 'kontak'),
            route('galeri.index'),
            route('berita.index'),
        ];

        foreach ($expected as $url) {
            $this->assertContains($url, $locations, "Sitemap tidak memuat {$url}.");
        }

        // Halaman dinamis program & berita juga disertakan lengkap dengan lastmod.
        foreach (Program::all() as $program) {
            $this->assertContains(route('program.show', $program), $locations);
        }
        foreach (Post::all() as $post) {
            $this->assertContains(route('berita.show', $post), $locations);
        }

        // prioritas beranda paling tinggi, dan tidak ada tautan admin/racun.
        $homeEntry = null;
        foreach ($xml->url as $url) {
            if ((string) $url->loc === route('home')) {
                $homeEntry = $url;
                break;
            }
        }
        $this->assertNotNull($homeEntry, 'Entri beranda tidak ditemukan pada sitemap.');
        $this->assertSame('1.0', (string) $homeEntry->priority);
        $this->assertDoesNotMatchRegularExpression('#/admin#', $body);
    }

    public function test_robots_txt_points_crawlers_at_the_sitemap(): void
    {
        $response = $this->get(route('robots'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_every_section_and_the_gallery_expose_schema_org_breadcrumbs(): void
    {
        $this->seed();

        $pages = [
            'profil' => route('site.section', 'profil'),
            'akademik' => route('site.section', 'akademik'),
            'kesiswaan' => route('site.section', 'kesiswaan'),
            'prestasi' => route('site.section', 'prestasi'),
            'industri' => route('site.section', 'industri'),
            'jepang' => route('site.section', 'jepang'),
            'ppdb' => route('site.section', 'ppdb'),
            'informasi' => route('site.section', 'informasi'),
            'alumni' => route('site.section', 'alumni'),
            'kontak' => route('site.section', 'kontak'),
            'Galeri kegiatan' => route('galeri.index'),
        ];

        foreach ($pages as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
            // Tiap blok adalah satu objek node tunggal; tidak ada JSON array
            // di tingkat atas agar Google membaca tiap node secara mandiri.
            $nodes = collect($matches[1])
                ->map(fn (string $json) => json_decode($json, true))
                ->each(fn ($node) => $this->assertIsArray($node))
                ->filter(fn ($node) => isset($node['@type']))
                ->all();

            $types = collect($nodes)->pluck('@type')->filter()->all();
            $this->assertContains('School', $types, "{$url} tanpa node School.");
            $this->assertContains('BreadcrumbList', $types, "{$url} tanpa node BreadcrumbList.");

            $breadcrumb = collect($nodes)->firstWhere('@type', 'BreadcrumbList');
            $this->assertSame('Beranda', $breadcrumb['itemListElement'][0]['name']);
            $this->assertSame(2, $breadcrumb['itemListElement'][1]['position']);
            $this->assertSame($url, $breadcrumb['itemListElement'][1]['item']);
        }
    }

    public function test_home_carries_the_school_schema_but_no_breadcrumb(): void
    {
        $this->seed();

        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $match);
        $school = collect(json_decode($match[1], true));

        $this->assertSame('School', $school['@type']);
        $this->assertNotContains('BreadcrumbList', [$school['@type']], 'Beranda tidak punya remah navigasi.');
        $this->assertSame('SMK INFOKOM', $school['name']);
        $this->assertSame(route('home'), $school['url']);
    }

    public function test_school_schema_follows_admin_settings_and_omits_empty_properties(): void
    {
        // Pengaturan tersimpan: nama, tahun berdiri, dan telepon ikut tampil.
        SiteSetting::updateOrCreate(['key' => 'school_name'], ['value' => 'SMK INFOKOM Bogor']);
        SiteSetting::updateOrCreate(['key' => 'profile_founded_year'], ['value' => '2005']);
        SiteSetting::updateOrCreate(['key' => 'school_phone_general'], ['value' => '(0251) 8348108']);

        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $match);
        $school = collect(json_decode($match[1], true));

        $this->assertSame('SMK INFOKOM Bogor', $school['name']);
        $this->assertSame('2005', $school['foundingDate']);
        $this->assertSame('(0251) 8348108', $school['telephone']);

        // Properti yang belum dikonfigurasi tidak ikut ditembak ke JSON.
        $this->assertArrayNotHasKey('email', $school->all());

        // Pengaturan dikosongkan lagi: telepon hilang, node tetap sah.
        SiteSetting::where('key', 'school_phone_general')->delete();
        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $match);
        $school = collect(json_decode($match[1], true));
        $this->assertArrayNotHasKey('telephone', $school->all());
    }

    public function test_news_and_program_pages_carry_deep_breadcrumbs(): void
    {
        $this->seed();

        // Indeks berita: dua tingkat — Beranda → Berita & Artikel.
        $breadcrumb = $this->firstJsonLd($this->get(route('berita.index'))->assertOk()->getContent(), 'BreadcrumbList');
        $this->assertCount(2, $breadcrumb['itemListElement']);
        $this->assertSame('Beranda', $breadcrumb['itemListElement'][0]['name']);
        $this->assertSame('Berita & Artikel', $breadcrumb['itemListElement'][1]['name']);
        $this->assertSame(route('berita.index'), $breadcrumb['itemListElement'][1]['item']);
        $this->assertSame(2, $breadcrumb['itemListElement'][1]['position']);

        // Detail berita: tiga tingkat — Beranda → Berita & Artikel → artikel.
        $post = Post::firstOrFail();
        $showHtml = $this->get(route('berita.show', $post))->assertOk()->getContent();
        $breadcrumb = $this->firstJsonLd($showHtml, 'BreadcrumbList');
        $items = $breadcrumb['itemListElement'];
        $this->assertCount(3, $items);
        $this->assertSame([1, 2, 3], array_column($items, 'position'));
        $this->assertSame('Beranda', $items[0]['name']);
        $this->assertSame('Berita & Artikel', $items[1]['name']);
        $this->assertSame(route('berita.index'), $items[1]['item']);
        $this->assertSame($post->title, $items[2]['name']);
        $this->assertSame(route('berita.show', $post), $items[2]['item']);

        // Detail program: tiga tingkat — Beranda → Program keahlian → program.
        $program = Program::firstOrFail();
        $programHtml = $this->get(route('program.show', $program))->assertOk()->getContent();
        $breadcrumb = $this->firstJsonLd($programHtml, 'BreadcrumbList');
        $items = $breadcrumb['itemListElement'];
        $this->assertCount(3, $items);
        $this->assertSame('Program keahlian', $items[1]['name']);
        $this->assertSame(route('site.section', 'akademik'), $items[1]['item']);
        $this->assertSame($program->name, $items[2]['name']);
        $this->assertSame(route('program.show', $program), $items[2]['item']);

        // Ketiga halaman tetap membawa node School dari layout.
        foreach ([$this->get(route('berita.index'))->getContent(), $showHtml, $programHtml] as $html) {
            $this->assertNotNull($this->firstJsonLd($html, 'School'));
        }
    }

    public function test_news_detail_carries_article_schema_and_share_meta(): void
    {
        $this->seed();

        $category = Category::firstOrCreate(
            ['type' => 'berita', 'slug' => 'artikel-uji'],
            ['name' => 'Kegiatan Sekolah'],
        );
        $post = Post::create([
            'category_id' => $category->id,
            'title' => 'Artikel uji data terstruktur',
            'excerpt' => 'Ringkasan singkat artikel untuk menguji schema Article dan og:image.',
            'body' => 'Paragraf isi artikel uji data terstruktur.',
            'published_at' => '2026-10-10',
            'art' => 1,
        ]);

        $html = $this->get(route('berita.show', $post))->assertOk()->getContent();

        // Bagikan tautan: tipe artikel, gambar share, dan meta waktu terbit.
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString(
            '<meta property="og:image" content="'.asset('images/logo-512.png').'">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta property="article:published_time" content="'.$post->published_at->toIso8601String().'">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta property="article:section" content="Kegiatan Sekolah">',
            $html,
        );

        // Rich result: node Article lengkap dengan identitas sekolah.
        $article = $this->firstJsonLd($html, 'Article');
        $this->assertNotNull($article, 'Detail berita tanpa node Article.');
        $this->assertSame('Article', $article['@type']);
        $this->assertSame('https://schema.org', $article['@context']);
        $this->assertSame($post->title, $article['headline']);
        $this->assertSame($post->excerpt, $article['description']);
        $this->assertSame(route('berita.show', $post), $article['url']);
        $this->assertSame(route('berita.show', $post), $article['mainEntityOfPage']['@id']);
        $this->assertSame($post->published_at->toIso8601String(), $article['datePublished']);
        $this->assertNotNull($article['dateModified'] ?? null, 'Article tanpa dateModified.');
        $this->assertSame('Kegiatan Sekolah', $article['articleSection']);
        $this->assertSame(asset('images/logo-512.png'), $article['image']);
        $this->assertSame('id', $article['inLanguage']);

        $school = \App\Support\StructuredData::school();
        foreach (['author', 'publisher'] as $key) {
            $this->assertSame('School', $article[$key]['@type'], "Article.{$key} bukan School.");
            $this->assertSame($school['@id'], $article[$key]['@id'], "Article.{$key} tidak merujuk node sekolah.");
        }
        $this->assertSame(512, $article['publisher']['logo']['width']);
        $this->assertSame(asset('images/logo-512.png'), $article['publisher']['logo']['url']);

        // Halaman daftar tetap memakai tipe website bawaan.
        $indexHtml = $this->get(route('berita.index'))->assertOk()->getContent();
        $this->assertStringContainsString('<meta property="og:type" content="website">', $indexHtml);
        $this->assertStringNotContainsString('article:published_time', $indexHtml);
    }

    public function test_guru_page_shows_leadership_directory_and_deep_breadcrumb(): void
    {
        $this->seed();

        $html = $this->get(route('guru.index'))->assertOk()->getContent();

        // Remah tiga tingkat: Beranda → Profil sekolah → halaman guru.
        $breadcrumb = $this->firstJsonLd($html, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb, 'Halaman guru tanpa remah navigasi.');
        $items = $breadcrumb['itemListElement'];
        $this->assertCount(3, $items);
        $this->assertSame([1, 2, 3], array_column($items, 'position'));
        $this->assertSame('Profil sekolah', $items[1]['name']);
        $this->assertSame(route('site.section', 'profil'), $items[1]['item']);
        $this->assertSame('Guru dan Tenaga Kependidikan', $items[2]['name']);
        $this->assertSame(route('guru.index'), $items[2]['item']);

        // Node sekolah tetap menyertai halaman ini.
        $this->assertNotNull($this->firstJsonLd($html, 'School'));

        // Data nyata: kepala sekolah dari seeder tampil sebagai pimpinan,
        // dan halaman ini memuat penjelasan direktori (bukan kosong total).
        $this->assertStringContainsString('Kepala Sekolah', $html);
        $this->assertStringContainsString('Ir. Hj. Liliek Rahmaningsih, M.M.Pd.', $html);
        $this->assertStringContainsString('Tenaga pendidik dan kependidikan', $html);
        $this->assertStringContainsString('panel admin', $html);

        // Menu Akademik menunjuk halaman direktori, bukan anchor profil lama.
        $navHtml = $this->get(route('site.section', 'akademik'))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.route('guru.index').'"', $navHtml);
        $this->assertStringNotContainsString('profil#guru', $navHtml);

        // Sitemap memuat halaman direktori.
        $sitemap = $this->get(route('sitemap'))->assertOk()->getContent();
        $this->assertStringContainsString(route('guru.index'), $sitemap);

        // Profil sekolah tetap menampilkan ringkasan guru + tautan direktori.
        $profileHtml = $this->get(route('site.section', 'profil'))->assertOk()->getContent();
        $this->assertStringContainsString('Buka direktori lengkap guru dan tenaga kependidikan', $profileHtml);
    }

    public function test_news_index_searches_filters_and_shows_featured(): void
    {
        $this->seed();

        // Berita utama: artikel terbit terbaru pada tampilan tanpa filter,
        // tampil menonjol beserta indikator jumlah artikel yang nyata.
        $latest = Post::published()->orderByDesc('published_at')->firstOrFail();
        $defaultHtml = $this->get(route('berita.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Berita utama', $defaultHtml);
        $this->assertStringContainsString($latest->title, $defaultHtml);
        $this->assertStringContainsString('artikel terbit', $defaultHtml);
        $this->assertStringContainsString('Cari artikel', $defaultHtml);

        // Pencarian teks: judul maupun isi artikel (server-side).
        Post::create([
            'category_id' => Category::firstOrCreate(['name' => 'Akademik', 'type' => 'berita'])->id,
            'title' => 'Kegiatan laboratorium semester genap',
            'excerpt' => 'Ringkasan kegiatan laboratorium.',
            'body' => 'Kata kunci cariumik hanya ada di isi artikel ini.',
            'published_at' => '2026-03-15',
            'art' => 2,
        ]);

        $this->get(route('berita.index', ['q' => 'cariumik']))
            ->assertOk()
            ->assertSee('Kegiatan laboratorium semester genap')
            ->assertSee('Menampilkan 1 artikel')
            ->assertSee('Tampilkan semua');

        // Pencarian tanpa hasil = empty state yang ramah, bukan halaman error.
        $this->get(route('berita.index', ['q' => 'katakunicangtidakada']))
            ->assertOk()
            ->assertSee('Tidak ada artikel yang cocok');

        // Filter tahun hanya menawarkan tahun yang benar-benar ada;
        // nilai tahun tak dikenal diabaikan dengan aman.
        $this->get(route('berita.index'))->assertOk()->assertSee('Semua tahun');
        $this->get(route('berita.index', ['tahun' => 'angka']))->assertOk();
        $this->get(route('berita.index', ['tahun' => '2026']))->assertOk();

        // Cukup artikel agar paginasi aktif: tautan halaman berikutnya
        // tetap membawa pencarian (filter tidak lepas).
        $category = Category::firstOrCreate(['name' => 'Kegiatan', 'type' => 'berita']);
        foreach (range(1, 12) as $index) {
            Post::create([
                'category_id' => $category->id,
                'title' => 'Artikel cariumik '.$index,
                'excerpt' => 'Ringkasan artikel cariumik.',
                'body' => 'Isi artikel cariumik.',
                'published_at' => '2026-04-10',
                'art' => 1,
            ]);
        }

        $this->get(route('berita.index', ['q' => 'cariumik']))
            ->assertOk()
            ->assertSee('q=cariumik', false);
    }

    public function test_draft_posts_are_hidden_from_public_pages(): void
    {
        $this->seed();

        $draft = Post::published()->orderByDesc('published_at')->firstOrFail();
        $draft->update(['is_published' => false]);

        // Draf: ditolak di detail, hilang dari indeks, beranda, dan sitemap.
        $this->get(route('berita.show', $draft))->assertNotFound();
        $this->get(route('berita.index'))->assertDontSee($draft->title);
        $this->get('/')->assertDontSee($draft->title);
        $this->get(route('sitemap'))->assertDontSee($draft->slug);
        $this->assertFalse(Post::query()->published()->whereKey($draft->id)->exists());
    }

    public function test_news_detail_shows_related_articles(): void
    {
        $this->seed();

        $category = Category::firstOrCreate(['name' => 'Akademik', 'type' => 'berita']);
        $main = Post::create([
            'category_id' => $category->id,
            'title' => 'Artikel utama untuk uji terkait',
            'excerpt' => 'Ringkasan artikel utama.',
            'body' => 'Isi artikel utama.',
            'published_at' => '2026-05-01',
            'art' => 1,
        ]);
        $sibling = Post::create([
            'category_id' => $category->id,
            'title' => 'Artikel saudara kategori sama',
            'excerpt' => 'Ringkasan saudara.',
            'body' => 'Isi saudara.',
            'published_at' => '2026-05-02',
            'art' => 2,
        ]);

        $html = $this->get(route('berita.show', $main))->assertOk()->getContent();

        $this->assertStringContainsString('Berita terkait', $html);
        $this->assertStringContainsString('Artikel saudara kategori sama', $html);

        // Dalam blok terkait hanya artikel lain yang tampil — artikel ini
        // sendiri tidak pernah menjadi tautan terkait (blok diambil dari
        // kelas section agar link kanonik di <head> tidak ikut dihitung).
        $relatedHtml = substr($html, (int) strpos($html, 'berita-related'));
        $this->assertStringContainsString('href="'.route('berita.show', $sibling).'"', $relatedHtml);
        $this->assertStringNotContainsString('href="'.route('berita.show', $main).'"', $relatedHtml);
    }

    public function test_alumni_page_shows_real_stories_japan_and_tracer_state(): void
    {
        $this->seed();

        $html = $this->get(route('site.section', 'alumni'))->assertOk()->getContent();

        // Cerita alumni nyata dari data tampil (nama + perjalanan).
        $this->assertStringContainsString('Perjalanan alumni', $html);
        foreach (Alumnus::all() as $alumnus) {
            $this->assertStringContainsString($alumnus->name, $html);
        }

        // Section Magang Jepang hanya tampil bila ada alumni yang
        // perjalanannya tercatat memuat "Jepang" (tidak dikarang).
        $hasJapan = Alumnus::where('role_desc', 'like', '%Jepang%')->exists();
        if ($hasJapan) {
            $this->assertStringContainsString('melangkah ke Jepang', $html);
        }

        // Tracer kosong → empty state jujur, bukan angka palsu.
        $this->assertStringContainsString('Data tracer study lulusan akan dipublikasikan', $html);
        $this->assertStringNotContainsString('class="tracer-list"', $html);

        // Setelah ada data tracer → bar tampil lengkap dengan catatan responden.
        TracerStat::create(['label' => 'Bekerja', 'value' => 60, 'color' => '#3446b4']);
        TracerStat::create(['label' => 'Melanjutkan pendidikan', 'value' => 30, 'color' => '#3b4a63']);
        $html = $this->get(route('site.section', 'alumni'))->assertOk()->getContent();
        $this->assertStringContainsString('class="tracer-list"', $html);
        $this->assertStringContainsString('Bekerja', $html);
        $this->assertStringContainsString('mewakili lulusan yang berhasil dihubungi', $html);

        // CTA kontribusi mengarah ke kanal kontak resmi (bukan tautan fiktif).
        $this->assertStringContainsString(route('site.section', 'kontak'), $html);
    }

    public function test_news_photos_show_on_public_pages_and_are_manageable_in_admin(): void
    {
        Storage::fake('public');
        $this->seed();

        // Seluruh artikel benih berfoto sesuai foto yang disediakan sekolah.
        $this->assertSame(6, Post::whereNotNull('image_path')->count());

        $post = Post::published()->whereNotNull('image_path')->orderByDesc('published_at')->firstOrFail();
        $photoUrl = asset('storage/'.$post->image_path);

        // Daftar berita: foto artikel tampil menggantikan ilustrasi gradien.
        $this->get(route('berita.index'))
            ->assertOk()
            ->assertSee($photoUrl, false);

        // Beranda: kartu kabar terbaru ikut menampilkan foto.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($photoUrl, false);

        // Artikel tanpa foto tetap memakai fallback ilustrasi (kolom art).
        Post::create([
            'title' => 'Artikel Tanpa Foto Uji',
            'excerpt' => 'Artikel uji tanpa foto unggulan.',
            'body' => 'Isi artikel uji.',
            'published_at' => '2026-01-15',
            'is_published' => true,
            'art' => 2,
        ]);
        $this->get(route('berita.index'))->assertOk()->assertSee('berita-art-', false);

        // Halaman detail: foto artikel tampil, og:image memakai foto, dan
        // node Article pada JSON-LD ikut menunjuk foto yang sama.
        $html = $this->get(route('berita.show', $post))->assertOk()->getContent();
        $this->assertStringContainsString($photoUrl, $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.$photoUrl.'"', $html);
        $article = $this->firstJsonLd($html, 'Article');
        $this->assertSame($photoUrl, $article['image'] ?? null);

        // Kesiswaan menampilkan foto kegiatan dari modul galeri.
        $this->get(route('site.section', 'kesiswaan'))
            ->assertOk()
            ->assertSee(asset('storage/photos/gallery/kegiatan-tkj.jpg'), false);

        // Admin Filament: daftar Posts kini menampilkan kolom foto.
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('filament.admin.resources.posts.index'))
            ->assertOk()
            ->assertSee('/storage/'.$post->image_path, false);

        // Unggah foto lewat form artikel (Livewire) → tersimpan pada disk publik.
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Artikel Berfoto Uji',
                'category_id' => Category::ofType('berita')->firstOrFail()->id,
                'published_at' => '2026-05-05',
                'is_published' => true,
                'excerpt' => 'Ringkasan artikel berfoto uji.',
                'body' => 'Isi artikel berfoto uji.',
                'image_path' => [UploadedFile::fake()->image('kegiatan-baru.jpg', 900, 600)],
            ])
            ->call('create');

        $created = Post::where('title', 'Artikel Berfoto Uji')->firstOrFail();
        $this->assertNotNull($created->image_path);
        $this->assertStringStartsWith('photos/posts/', $created->image_path);
        Storage::disk('public')->assertExists($created->image_path);

        // Ganti foto (alur penggantian panel: lepas foto lama, unggah baru):
        // foto lama ikut dibersihkan (tidak ada berkas yatim).
        $oldPath = $created->image_path;
        Livewire::test(EditPost::class, ['record' => $created->getKey()])
            ->fillForm(['image_path' => []])
            ->fillForm(['image_path' => [UploadedFile::fake()->image('pengganti.jpg', 640, 480)]])
            ->call('save');

        $created->refresh();
        $this->assertNotSame($oldPath, $created->image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($created->image_path);

        // Hapus catutan: foto terakhir ikut terhapus dari disk.
        $finalPath = $created->image_path;
        $created->delete();
        Storage::disk('public')->assertMissing($finalPath);
    }

    public function test_program_photos_show_on_akademik_and_are_manageable_in_admin(): void
    {
        Storage::fake('public');
        $this->seed();

        // Empat jurusan berfoto sesuai jurusan.zip; kartu berfoto memakai foto.
        $this->assertSame(4, Program::whereNotNull('photo_path')->count());

        $program = Program::where('short', 'TKJ')->firstOrFail();
        $photoUrl = asset('storage/'.$program->photo_path);

        $akademik = $this->get(route('site.section', 'akademik'))->assertOk();
        $akademik->assertSee($photoUrl, false)->assertSee('program-card', false);

        $this->get(route('program.show', $program))
            ->assertOk()
            ->assertSee($photoUrl, false)
            ->assertSee('program-hero-photo', false);

        // Kartu tanpa foto tetap memakai kartu teks lengkap (fallback).
        Program::create([
            'short' => 'UJI',
            'icon' => 'code',
            'name' => 'Program Uji Tanpa Foto',
            'description' => 'Deskripsi uji tanpa foto.',
            'skills' => ['Satu'],
            'jobs' => ['Dua'],
            'facilities' => ['Tiga'],
        ]);
        $this->get(route('site.section', 'akademik'))
            ->assertOk()
            ->assertSee('Program Uji Tanpa Foto')
            ->assertSee('>Kompetensi</h3>', false);

        // Admin modul konten: unggah foto jurusan lewat panel.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $payload = [
            'short' => 'UJI2',
            'icon' => 'network',
            'name' => 'Program Uji Berfoto',
            'description' => 'Deskripsi uji berfoto.',
            'skills' => "Satu\nDua",
            'jobs' => "Tiga\nEmpat",
            'facilities' => "Lima\nEnam",
        ];

        $this->post(route('admin.content.store', 'akademik'), [
            ...$payload,
            'photo_path' => UploadedFile::fake()->image('jurusan-baru.jpg', 900, 600),
        ])->assertRedirect(route('admin.content.index', 'akademik'));

        $created = Program::where('short', 'UJI2')->firstOrFail();
        $this->assertNotNull($created->photo_path);
        $this->assertStringStartsWith('photos/programs/', $created->photo_path);
        Storage::disk('public')->assertExists($created->photo_path);

        // Halaman publik langsung memakai foto hasil unggahan admin.
        $this->get(route('site.section', 'akademik'))
            ->assertOk()
            ->assertSee(asset('storage/'.$created->photo_path), false);

        // Ganti foto: foto lama ikut terhapus (tidak ada berkas yatim).
        $oldPath = $created->photo_path;
        $this->put(route('admin.content.update', ['akademik', $created->id]), [
            ...$payload,
            'photo_path' => UploadedFile::fake()->image('jurusan-ganti.jpg', 800, 600),
        ])->assertRedirect(route('admin.content.index', 'akademik'));

        $created->refresh();
        $this->assertNotSame($oldPath, $created->photo_path);
        Storage::disk('public')->assertMissing($oldPath);

        // Centang "Hapus foto": kolom kosong dan berkas ikut dibersihkan.
        $secondPath = $created->photo_path;
        $this->put(route('admin.content.update', ['akademik', $created->id]), [
            ...$payload,
            'photo_path_remove' => '1',
        ])->assertRedirect(route('admin.content.index', 'akademik'));

        $this->assertNull($created->refresh()->photo_path);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_structure_chart_is_filled_from_real_teacher_records(): void
    {
        $this->seed();

        $html = $this->get(route('site.section', 'profil'))->assertOk()->getContent();

        // Ambil hanya blok struktur (sampai awal bagian direktori guru).
        $this->assertSame(
            1,
            preg_match('/id="struktur-organisasi"(.*?)id="guru"/s', $html, $matches),
            'Bagian struktur organisasi tidak ditemukan pada /profil.',
        );
        $struktur = $matches[1];

        // Tiap tingkat berisi nama guru sungguhan dari catatan panel admin:
        // wakil kepala sekolah dan ketua program keahlian tampil berfoto.
        $names = Teacher::query()
            ->where(fn ($query) => $query->where('position', 'like', 'Wakil Kepala%')
                ->orWhere('position', 'like', '%Kepala Program Keahlian%')
                ->orWhere('position', 'like', 'Kepala Tata Usaha'))
            ->pluck('name');

        $this->assertGreaterThanOrEqual(9, $names->count(), 'Data wakil/ketua tidak ditemukan.');

        foreach ($names as $name) {
            $this->assertStringContainsString(e($name), $struktur, "Struktur tidak memuat guru: {$name}");
        }

        // Foto guru pada bagian struktur memakai berkas storage nyata.
        $this->assertMatchesRegularExpression('#<img[^>]+src="[^"]*storage/photos/teachers/[^"]+"#', $struktur);

        // Label jabatan karangan dan kartu inisial placeholder lama dilepas.
        $this->assertStringNotContainsString('Wakil Kepala Bidang Kurikulum', $struktur);
        $this->assertStringNotContainsString('photo-placeholder">WKS', $struktur);
        $this->assertStringNotContainsString('photo-placeholder">PK', $struktur);

        // Ringkasan akhir menautkan direktori lengkap guru & tenaga.
        $this->assertStringContainsString(route('guru.index'), $struktur);
    }

    public function test_home_profile_photo_shows_and_is_editable_in_admin(): void
    {
        Storage::fake('public');
        $this->seed();

        // Foto benih bagian Profil beranda (gedung sekolah) tampil di beranda.
        $seededUrl = asset('storage/photos/home/gedung-sekolah.jpg');
        $this->get(route('home'))->assertOk()->assertSee($seededUrl, false);

        // Admin mengganti foto lewat panel Pengaturan Profil.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SchoolProfileSettings::class)
            ->set('data.home_photo', [UploadedFile::fake()->image('gedung-baru.jpg', 1200, 800)])
            ->call('save');

        $newPath = SiteSetting::where('key', 'profile_home_photo')->value('value');
        $this->assertNotNull($newPath);
        $this->assertStringStartsWith('photos/home/', $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing('photos/home/gedung-sekolah.jpg');

        $this->get(route('home'))->assertOk()->assertSee(asset('storage/'.$newPath), false);

        // Kosongkan foto: key dilepas, berkas dibersihkan, beranda tanpa foto.
        Livewire::test(SchoolProfileSettings::class)
            ->set('data.home_photo', [])
            ->call('save');

        $this->assertNull(SiteSetting::where('key', 'profile_home_photo')->value('value'));
        Storage::disk('public')->assertMissing($newPath);
        $this->get(route('home'))->assertOk()->assertDontSee(asset('storage/'.$newPath), false);
    }

    public function test_school_logo_editable_in_admin_and_shown_on_site(): void
    {
        Storage::fake('public');
        $this->seed();

        // Logo bawaan dipakai selama admin belum mengganti logo.
        $this->get(route('home'))->assertOk()->assertSee(asset('images/logo-192.png'), false);

        // Admin mengganti logo lewat panel Pengaturan Profil.
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SchoolProfileSettings::class)
            ->set('data.school_logo', [UploadedFile::fake()->image('logo-baru.png', 512, 512)])
            ->call('save');

        $path = SchoolSettings::logoPath();
        $this->assertNotNull($path);
        $this->assertStringStartsWith('photos/brand/', $path);
        Storage::disk('public')->assertExists($path);

        // Logo baru tampil di header/footer beranda dan metadata og:image.
        $home = $this->get(route('home'));
        $home->assertOk()->assertSee(asset('storage/'.$path), false);
        $home->assertSee('<meta property="og:image" content="'.asset('storage/'.$path).'"', false);

        // Kosongkan logo: kembali ke berkas bawaan dan berkas lama dibersihkan.
        Livewire::test(SchoolProfileSettings::class)
            ->set('data.school_logo', [])
            ->call('save');

        $this->assertNull(SchoolSettings::logoPath());
        Storage::disk('public')->assertMissing($path);
        $this->get(route('home'))->assertOk()->assertSee(asset('images/logo-192.png'), false);
    }

    public function test_admin_panel_navigation_lists_every_content_module(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $html = $this->get(route('filament.admin.pages.dashboard'))->assertOk()->getContent();

        // Seluruh modul konten & pengaturan tampil di navigasi panel admin,
        // sehingga semua bisa dikelola tanpa mengetik URL manual.
        foreach ([
            'Berita', 'Kategori Berita', 'Galeri', 'Pengaturan Profil',
            'Program keahlian', 'Guru & TTK', 'Fasilitas sekolah', 'Prestasi',
            'Mitra industri', 'Magang Jepang', 'Reel Magang Jepang', 'Alumni',
            'Tracer study', 'Agenda', 'Pengumuman', 'Lowongan BKK',
            'Pengaturan sekolah', 'Pendaftar PPDB', 'Pesan masuk',
        ] as $label) {
            $this->assertStringContainsString(e($label), $html, "Navigasi panel admin tidak memuat: {$label}");
        }

        foreach ([
            'akademik', 'guru', 'fasilitas', 'prestasi', 'industri', 'jepang',
            'reel-jepang', 'alumni', 'tracer', 'agenda', 'pengumuman', 'lowongan',
        ] as $slug) {
            $this->assertStringContainsString('/admin/content/'.$slug, $html, "Navigasi panel admin tidak menautkan modul: {$slug}");
        }

        $this->assertStringContainsString('/admin/settings', $html);
        $this->assertStringContainsString('/admin/ppdb', $html);
        $this->assertStringContainsString('/admin/pesan', $html);
    }

    /** Ambil satu node JSON-LD berdasarkan @type dari sebuah halaman HTML. */
    private function firstJsonLd(string $html, string $type): ?array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        foreach ($matches[1] as $json) {
            $node = json_decode($json, true);
            if (is_array($node) && ($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        return null;
    }
}
