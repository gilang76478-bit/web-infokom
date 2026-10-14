<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        foreach (['profil', 'akademik', 'kesiswaan', 'prestasi', 'industri', 'jepang', 'ppdb', 'informasi', 'galeri', 'alumni', 'kontak'] as $section) {
            $this->get(route('site.section', $section))->assertOk();
        }
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
            ['GET', 'admin/settings'],
            ['PUT', 'admin/settings'],
            ['GET', 'admin/ppdb'],
            ['GET', 'admin/pesan'],
            ['GET', 'admin/content/berita'],
            ['GET', 'admin/content/berita/create'],
            ['POST', 'admin/content/berita'],
            ['GET', 'admin/content/berita/1/edit'],
            ['PUT', 'admin/content/berita/1'],
            ['DELETE', 'admin/content/berita/1'],
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

    public function test_admin_can_create_update_and_delete_news(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.content.index', 'berita'))->assertOk();
        $this->get(route('admin.content.create', 'berita'))->assertOk();

        $data = [
            'category' => 'Kegiatan',
            'title' => 'Kegiatan sekolah terbaru',
            'excerpt' => 'Informasi kegiatan sekolah.',
            'published_at' => '2026-10-01',
            'art' => 1,
        ];

        $this->post(route('admin.content.store', 'berita'), $data)
            ->assertRedirect(route('admin.content.index', 'berita'));

        $post = Post::firstOrFail();
        $this->put(route('admin.content.update', ['berita', $post->id]), [...$data, 'title' => 'Judul diperbarui'])
            ->assertRedirect(route('admin.content.index', 'berita'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Judul diperbarui']);

        $this->delete(route('admin.content.destroy', ['berita', $post->id]))
            ->assertRedirect(route('admin.content.index', 'berita'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
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

        foreach (['akademik', 'berita', 'agenda', 'prestasi', 'galeri', 'jepang', 'industri', 'alumni', 'tracer', 'pengumuman'] as $type) {
            $this->get(route('admin.content.index', $type))->assertOk();
            $this->get(route('admin.content.create', $type))->assertOk();
        }
    }
}
