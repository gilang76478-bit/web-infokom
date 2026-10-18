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
use App\Models\GalleryItem;
use App\Models\JapanStep;
use App\Models\Partner;
use App\Models\Post;
use App\Models\PpdbApplicant;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\Teacher;
use App\Models\TracerStat;
use App\Models\User;
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
            ->assertSee('Laboratorium Komputer')
            ->assertSee('Lengkapi melalui pengaturan sekolah');
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
        SiteSetting::updateOrCreate(
            ['key' => 'profile_facilities'],
            ['value' => 'Ruang Server|Pusat perangkat jaringan sekolah'],
        );

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Visi hasil pengaturan admin')
            ->assertDontSee('Menjadi sekolah kejuruan yang unggul')
            ->assertSee('20998877')
            ->assertSee('Misi pertama dari admin')
            ->assertSee('Misi kedua dari admin')
            ->assertSee('Ruang Server')
            ->assertSee('Pusat perangkat jaringan sekolah')
            ->assertDontSee('Laboratorium Komputer');
    }

    public function test_profile_page_renders_history_entries_and_principal_from_settings(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'profile_history'],
            ['value' => "2005|SMK INFOKOM didirikan|Berdiri pada 10 Mei 2005.\n2023|Akreditasi A|Peringkat akreditasi A."],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'profile_principal_name'],
            ['value' => 'Ir. Hj. Liliek Rahmaningsih, M.MPd'],
        );

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('SMK INFOKOM didirikan')
            ->assertSee('Berdiri pada 10 Mei 2005.')
            ->assertSee('Akreditasi A')
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.MPd')
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
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.MPd')
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
            ->set('data.facilities', 'Ruang Data Center|Pusat server sekolah')
            ->set('data.history', '2005|Berdiri|Didirikan pada 10 Mei 2005.')
            ->call('save');

        $this->assertDatabaseHas('site_settings', ['key' => 'profile_vision', 'value' => 'Visi versi uji admin']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_mission', 'value' => "Misi uji satu\nMisi uji dua"]);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_npsn', 'value' => '12345678']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_facilities', 'value' => 'Ruang Data Center|Pusat server sekolah']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_history', 'value' => '2005|Berdiri|Didirikan pada 10 Mei 2005.']);

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Visi versi uji admin')
            ->assertSee('Ruang Data Center')
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
        $this->assertSame(
            ['PT. Guna Senaputra Sejahtera', 'Raja Promo Advertising'],
            Partner::orderBy('name')->pluck('name')->all(),
        );
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
            ->assertSee('Data tracer study belum tersedia.')
            ->assertDontSee('“”');

        $this->get(route('site.section', 'industri'))
            ->assertOk()
            ->assertSee('PT. Guna Senaputra Sejahtera')
            ->assertSee('Raja Promo Advertising')
            ->assertDontSee('Cakrawala Teknologi');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('PT. Guna Senaputra Sejahtera')
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

        // Satu foto "diunggah" (lolos validasi Filament pada test lain),
        // ditampilkan sebagai <img> dengan atribut performa lazy loading.
        Storage::disk('public')->put('gallery/foto-uji.jpg', 'bytes-gambar-uji');
        GalleryItem::whereNull('image_path')->first()->update(['image_path' => 'gallery/foto-uji.jpg']);

        $listing = $this->get(route('galeri.index'));
        $listing
            ->assertOk()
            ->assertSee('Galeri Kegiatan')
            ->assertSee(asset('storage/gallery/foto-uji.jpg'), false)
            ->assertSee('loading="lazy"', false);

        // Item tanpa foto memakai fallback ilustrasi (kolom art).
        $listing->assertSee('galeri-art-', false);

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

        foreach (['akademik', 'guru', 'agenda', 'prestasi', 'jepang', 'industri', 'alumni', 'tracer', 'pengumuman'] as $type) {
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
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.MPd')
            ->assertDontSee('Syaiful')
            ->assertDontSee('Rina Wulandari')
            ->assertDontSee('Ahmad Hidayat');

        // Hanya baris kepala sekolah yang terverifikasi tersisa pada data contoh.
        $this->assertSame(1, Teacher::count());
        $this->assertSame('Kepala Sekolah', Teacher::firstOrFail()->position);
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
            ->assertSee('Ir. Hj. Liliek Rahmaningsih, M.MPd')
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
}
