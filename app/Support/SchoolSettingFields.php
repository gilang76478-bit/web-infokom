<?php

namespace App\Support;

/**
 * Definisi field halaman Pengaturan Sekolah: label + aturan validasi.
 * Dipakai bersama oleh AdminSettingsController (tampilan & simpan) dan
 * AdminSettingsRequest (validasi), sebagai pengganti konstanta privat
 * yang tadinya inline di dalam controller.
 */
class SchoolSettingFields
{
    public const ALL = [
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
}
