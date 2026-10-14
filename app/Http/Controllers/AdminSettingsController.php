<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    private const FIELDS = [
        'school_name' => ['label' => 'Nama sekolah', 'rules' => 'nullable|string|max:255'],
        'school_address_line' => ['label' => 'Alamat lengkap', 'rules' => 'nullable|string|max:255'],
        'school_address_rtrw' => ['label' => 'RT/RW', 'rules' => 'nullable|string|max:80'],
        'school_address_village' => ['label' => 'Kelurahan/Desa', 'rules' => 'nullable|string|max:120'],
        'school_address_district' => ['label' => 'Kecamatan', 'rules' => 'nullable|string|max:120'],
        'school_address_city' => ['label' => 'Kota/Kabupaten', 'rules' => 'nullable|string|max:120'],
        'school_address_province' => ['label' => 'Provinsi', 'rules' => 'nullable|string|max:120'],
        'school_address_postal_code' => ['label' => 'Kode pos', 'rules' => 'nullable|string|max:20'],
        'school_phone_general' => ['label' => 'Telepon umum', 'rules' => 'nullable|string|max:80'],
        'school_phone_admin' => ['label' => 'Telepon administrasi', 'rules' => 'nullable|string|max:80'],
        'school_phone_ppdb' => ['label' => 'Telepon PPDB', 'rules' => 'nullable|string|max:80'],
        'school_phone_industry' => ['label' => 'Telepon kerja sama', 'rules' => 'nullable|string|max:80'],
        'school_phone_alumni' => ['label' => 'Telepon alumni', 'rules' => 'nullable|string|max:80'],
        'school_whatsapp_general' => ['label' => 'WhatsApp umum', 'rules' => 'nullable|string|max:80'],
        'school_whatsapp_ppdb' => ['label' => 'WhatsApp PPDB', 'rules' => 'nullable|string|max:80'],
        'school_email_general' => ['label' => 'Email umum', 'rules' => 'nullable|email|max:190'],
        'school_email_ppdb' => ['label' => 'Email PPDB', 'rules' => 'nullable|email|max:190'],
        'school_email_admin' => ['label' => 'Email administrasi', 'rules' => 'nullable|email|max:190'],
        'school_email_industry' => ['label' => 'Email kerja sama', 'rules' => 'nullable|email|max:190'],
        'school_email_alumni' => ['label' => 'Email alumni', 'rules' => 'nullable|email|max:190'],
        'school_maps_url' => ['label' => 'Google Maps URL', 'rules' => 'nullable|url|max:255'],
        'school_instagram_url' => ['label' => 'Instagram', 'rules' => 'nullable|url|max:255'],
        'school_tiktok_url' => ['label' => 'TikTok', 'rules' => 'nullable|url|max:255'],
        'school_facebook_url' => ['label' => 'Facebook', 'rules' => 'nullable|url|max:255'],
        'school_youtube_url' => ['label' => 'YouTube', 'rules' => 'nullable|url|max:255'],
        'school_other_social_url' => ['label' => 'Sosial media lain', 'rules' => 'nullable|url|max:255'],
        'school_hours' => ['label' => 'Jam operasional (setiap baris: Hari|Jam)', 'rules' => 'nullable|string|max:2000'],
    ];

    public function index(): View
    {
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
            });
        }

        $settings = SiteSetting::pluck('value', 'key')->all();

        return view('admin.settings.index', [
            'fields' => self::FIELDS,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
            });
        }

        $data = $request->validate(
            collect(self::FIELDS)->mapWithKeys(fn ($field, $key) => [$key => $field['rules']])->all()
        );

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                SiteSetting::where('key', $key)->delete();
                continue;
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        return redirect()->route('admin.settings.index')->with('status', 'Pengaturan sekolah berhasil disimpan.');
    }
}
