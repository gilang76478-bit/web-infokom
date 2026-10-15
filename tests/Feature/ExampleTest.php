<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PpdbApplicant;
use App\Models\User;
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

    public function test_public_navigation_pages_are_available(): void
    {
        foreach (['profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang', 'ppdb', 'informasi', 'alumni', 'kontak'] as $section) {
            $this->get(route('site.section', $section))->assertOk();
        }

        // Galeri kini mempunyai rute sendiri dengan filter kategori.
        $this->get(route('galeri.index'))->assertOk();
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
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'profile_vision'],
            ['value' => 'Visi hasil pengaturan admin'],
        );
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'profile_npsn'],
            ['value' => '20998877'],
        );
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'profile_mission'],
            ['value' => "Misi pertama dari admin\nMisi kedua dari admin"],
        );
        \App\Models\SiteSetting::updateOrCreate(
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

    public function test_admin_can_save_profile_settings_from_filament_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Filament\Pages\SchoolProfileSettings::class)
            ->set('data.vision', 'Visi versi uji admin')
            ->set('data.mission', "Misi uji satu\nMisi uji dua")
            ->set('data.npsn', '12345678')
            ->set('data.facilities', 'Ruang Data Center|Pusat server sekolah')
            ->call('save');

        $this->assertDatabaseHas('site_settings', ['key' => 'profile_vision', 'value' => 'Visi versi uji admin']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_mission', 'value' => "Misi uji satu\nMisi uji dua"]);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_npsn', 'value' => '12345678']);
        $this->assertDatabaseHas('site_settings', ['key' => 'profile_facilities', 'value' => 'Ruang Data Center|Pusat server sekolah']);

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Visi versi uji admin')
            ->assertSee('Ruang Data Center')
            ->assertSee('12345678');
    }

    public function test_every_admin_route_requires_admin_privileges(): void
    {
        $message = \App\Models\ContactMessage::create([
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
            'program' => 'PPLG',
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
            ['PATCH', 'admin/pesan/' . $message->id . '/read'],
            ['PATCH', 'admin/ppdb/' . $applicant->id],
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
    }

    public function test_custom_error_pages_are_used_instead_of_framework_defaults(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertStatus(404)
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke beranda', false);

        // Halaman 500 hanya dipakai saat APP_DEBUG=false (mode produksi).
        config(['app.debug' => false]);
        $response = app(\Illuminate\Foundation\Exceptions\Handler::class)->render(
            \Illuminate\Http\Request::create('/', 'GET'),
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
        $message = \App\Models\ContactMessage::create([
            'name' => 'Pengunjung',
            'email' => 'visitor@example.test',
            'whatsapp' => '081234567890',
            'subject' => 'Umum',
            'message' => 'Saya ingin bertanya tentang kunjungan sekolah.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Pengunjung');
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

        $category = \App\Models\Category::ofType('berita')->firstOrFail();

        // Menulis dan menerbitkan artikel baru (slug dibuat otomatis).
        \Livewire\Livewire::test(\App\Filament\Resources\Posts\Pages\CreatePost::class)
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
        \Livewire\Livewire::test(\App\Filament\Resources\Posts\Pages\EditPost::class, ['record' => $post->getKey()])
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
        $handler = app(\App\Http\Middleware\SecurityHeaders::class);

        $build = $handler->handle(
            \Illuminate\Http\Request::create('/build/assets/site-abc123.css', 'GET'),
            fn () => new \Symfony\Component\HttpFoundation\Response('css', 200),
        );
        // Symfony menormalkan urutan token Cache-Control; verifikasi tiap direktif.
        $buildCache = (string) $build->headers->get('Cache-Control');
        $this->assertStringContainsString('immutable', $buildCache);
        $this->assertStringContainsString('max-age=31536000', $buildCache);
        $this->assertStringContainsString('public', $buildCache);

        $missing = $handler->handle(
            \Illuminate\Http\Request::create('/build/assets/hilang.css', 'GET'),
            fn () => new \Symfony\Component\HttpFoundation\Response('tidak ada', 404),
        );
        $this->assertStringNotContainsString('immutable', (string) $missing->headers->get('Cache-Control'));

        $storage = $handler->handle(
            \Illuminate\Http\Request::create('/storage/gallery/foto.jpg', 'GET'),
            fn () => new \Symfony\Component\HttpFoundation\Response('gambar', 200),
        );
        $storageCache = (string) $storage->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=604800', $storageCache);
        $this->assertStringContainsString('public', $storageCache);
    }

    public function test_admin_can_manage_categories_in_filament_with_auto_slug(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        \Livewire\Livewire::test(\App\Filament\Resources\Categories\Pages\CreateCategory::class)
            ->fillForm(['name' => 'Kegiatan Ekstrakurikuler', 'type' => 'berita'])
            ->call('create');

        $category = \App\Models\Category::where('name', 'Kegiatan Ekstrakurikuler')->firstOrFail();

        $this->assertSame('kegiatan-ekstrakurikuler', $category->slug);
        $this->assertSame('berita', $category->type);
    }

    public function test_news_list_page_filters_by_category(): void
    {
        $this->seed();

        $listing = $this->get(route('berita.index'));
        $listing
            ->assertOk()
            ->assertSee('Berita & Artikel')
            ->assertSee('PPDB 2026 gelombang 2 resmi dibuka')
            ->assertSee('Kunjungan industri siswa TJKT ke pusat data');

        // Tautan tiap kartu mengarah ke halaman detail per slug.
        foreach (Post::query()->pluck('slug') as $slug) {
            $listing->assertSee('/berita/' . $slug, false);
        }

        // Filter kategori hanya menampilkan artikel kategori tersebut.
        $this->get(route('berita.index', ['kategori' => 'ppdb']))
            ->assertOk()
            ->assertSee('PPDB 2026 gelombang 2 resmi dibuka')
            ->assertDontSee('Kunjungan industri siswa TJKT ke pusat data');

        // Kategori tidak dikenal → 404 (halaman error kustom).
        $this->get(route('berita.index', ['kategori' => 'kategori-tidak-ada']))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_news_detail_page_shows_the_full_article(): void
    {
        $this->seed();

        $post = Post::where('slug', 'ppdb-2026-gelombang-2-resmi-dibuka')->firstOrFail();

        $this->get(route('berita.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->excerpt)
            ->assertSee('Penerimaan Peserta Didik Baru (PPDB) 2026 gelombang 2 resmi dibuka')
            ->assertSee('Persyaratan lengkap');

        $this->assertNotNull($post->category);
        $this->assertSame('PPDB', $post->category->name);

        $this->get('/berita/artikel-yang-tidak-ada')->assertNotFound();
    }

    public function test_gallery_page_shows_photos_with_lazy_loading_and_category_filter(): void
    {
        Storage::fake('public');
        $this->seed();

        // Satu foto "diunggah" (lolos validasi Filament pada test lain),
        // ditampilkan sebagai <img> dengan atribut performa lazy loading.
        Storage::disk('public')->put('gallery/foto-uji.jpg', 'bytes-gambar-uji');
        \App\Models\GalleryItem::whereNull('image_path')->first()->update(['image_path' => 'gallery/foto-uji.jpg']);

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
            ->assertSee('Praktik jaringan di Lab TJKT')
            ->assertDontSee('Upacara Hari Kemerdekaan');

        // Kategori tidak dikenal → 404 (halaman error kustom).
        $this->get(route('galeri.index', ['kategori' => 'tidak-ada']))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_admin_can_upload_a_gallery_photo_in_filament(): void
    {
        Storage::fake('public');
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $category = \App\Models\Category::ofType('galeri')->firstOrFail();
        $file = UploadedFile::fake()->image('kegiatan-lab.jpg', 800, 600);

        $this->get(route('filament.admin.resources.gallery-items.index'))->assertOk();

        \Livewire\Livewire::test(\App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem::class)
            ->fillForm([
                'title' => 'Foto Unggahan Uji',
                'category_id' => $category->id,
                'size' => 'w',
                'art' => 3,
                'image_path' => [$file],
            ])
            ->call('create');

        $item = \App\Models\GalleryItem::where('title', 'Foto Unggahan Uji')->firstOrFail();

        $this->assertNotNull($item->image_path);
        $this->assertSame($category->id, $item->category_id);
        Storage::disk('public')->assertExists($item->image_path);

        // Foto hasil unggahan tampil di halaman galeri publik.
        $this->get(route('galeri.index'))
            ->assertOk()
            ->assertSee('Foto Unggahan Uji')
            ->assertSee(asset('storage/' . $item->image_path), false);

        // File ikut terhapus ketika record dihapus (tidak ada file yatim).
        $path = $item->image_path;
        $item->delete();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_ppdb_form_stores_an_application_and_returns_a_number(): void
    {
        $this->seed();
        $program = \App\Models\Program::firstOrFail();

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
            'program' => 'PPLG',
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
        $program = \App\Models\Program::create([
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
        $duplicate = \App\Models\Program::create([
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
        $program = \App\Models\Program::firstOrFail();

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
        $program = \App\Models\Program::firstOrFail();
        $url = route('program.show', $program);

        $this->get(route('site.section', 'akademik'))
            ->assertOk()
            ->assertSee($url, false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($url, false);
    }

    public function test_teachers_are_shown_on_profile_and_program_detail_with_relations(): void
    {
        $this->seed();

        $teacher = \App\Models\Teacher::whereNotNull('program_id')->firstOrFail();

        // Relasi Eloquent dua arah: belongsTo dan hasMany.
        $this->assertNotNull($teacher->program);
        $this->assertTrue($teacher->program->teachers->contains($teacher));
        $this->assertSame($teacher->program->id, $teacher->program_id);

        $this->get(route('site.section', 'profil'))
            ->assertOk()
            ->assertSee('Guru & Tenaga Kependidikan')
            ->assertSee($teacher->name)
            ->assertSee($teacher->nip);

        $this->get(route('program.show', $teacher->program))
            ->assertOk()
            ->assertSee($teacher->name);
    }

    public function test_admin_can_manage_teachers(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $program = \App\Models\Program::firstOrFail();

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

        $teacher = \App\Models\Teacher::where('name', 'Guru Baru Produksi')->firstOrFail();
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
