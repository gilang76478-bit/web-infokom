<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class SchoolSettings
{
    public static function contact(): array
    {
        $defaults = [
            'name' => 'SMK INFOKOM',
            'address' => [
                'Alamat lengkap' => 'Jl. Raya Cibinong No. 88, Kelurahan Cibinong, Kecamatan Cibinong',
                'RT/RW' => '003/012',
                'Kelurahan/Desa' => 'Cibinong',
                'Kecamatan' => 'Cibinong',
                'Kota/Kabupaten' => 'Bogor',
                'Provinsi' => 'Jawa Barat',
                'Kode pos' => '16915',
            ],
            'phones' => [
                'general' => '+62 812-3456-7890',
                'administration' => '+62 812-3456-7891',
                'ppdb' => '+62 812-3456-7892',
                'industry' => '+62 812-3456-7893',
                'alumni' => '+62 812-3456-7894',
            ],
            'whatsapp' => [
                'general' => '+62 812-3456-7890',
                'ppdb' => '+62 812-3456-7892',
            ],
            'emails' => [
                'general' => 'info@smkinfokom.sch.id',
                'ppdb' => 'ppdb@smkinfokom.sch.id',
                'administration' => 'admin@smkinfokom.sch.id',
                'industry' => 'industry@smkinfokom.sch.id',
                'alumni' => 'alumni@smkinfokom.sch.id',
            ],
            'maps_url' => 'https://maps.google.com/?q=SMK%20INFOKOM%20Bogor',
            'socials' => [
                'Instagram' => 'https://instagram.com/smkinfokom',
                'TikTok' => 'https://tiktok.com/@smkinfokom',
                'Facebook' => 'https://facebook.com/smkinfokom',
                'YouTube' => 'https://youtube.com/@smkinfokom',
                'Kanal resmi lainnya' => 'https://smkinfokom.sch.id',
            ],
            'hours' => [
                ['day' => 'Senin-Kamis', 'time' => '07.00-15.00'],
                ['day' => 'Jumat', 'time' => '07.00-11.30'],
                ['day' => 'Sabtu', 'time' => '07.00-13.00'],
                ['day' => 'Minggu', 'time' => 'Tutup'],
            ],
            'inquiry_types' => [
                'Umum',
                'PPDB',
                'Administrasi',
                'Kerja sama',
                'Hubungan industri',
                'Alumni',
                'Lainnya',
            ],
        ];

        $merged = array_replace_recursive($defaults, config('school.contact', []));

        if (! Schema::hasTable('site_settings')) {
            return $merged;
        }

        $mapping = [
            'school_name' => 'name',
            'school_address_line' => 'address.Alamat lengkap',
            'school_address_rtrw' => 'address.RT/RW',
            'school_address_village' => 'address.Kelurahan/Desa',
            'school_address_district' => 'address.Kecamatan',
            'school_address_city' => 'address.Kota/Kabupaten',
            'school_address_province' => 'address.Provinsi',
            'school_address_postal_code' => 'address.Kode pos',
            'school_phone_general' => 'phones.general',
            'school_phone_admin' => 'phones.administration',
            'school_phone_ppdb' => 'phones.ppdb',
            'school_phone_industry' => 'phones.industry',
            'school_phone_alumni' => 'phones.alumni',
            'school_whatsapp_general' => 'whatsapp.general',
            'school_whatsapp_ppdb' => 'whatsapp.ppdb',
            'school_email_general' => 'emails.general',
            'school_email_ppdb' => 'emails.ppdb',
            'school_email_admin' => 'emails.administration',
            'school_email_industry' => 'emails.industry',
            'school_email_alumni' => 'emails.alumni',
            'school_maps_url' => 'maps_url',
            'school_instagram_url' => 'socials.Instagram',
            'school_tiktok_url' => 'socials.TikTok',
            'school_facebook_url' => 'socials.Facebook',
            'school_youtube_url' => 'socials.YouTube',
            'school_other_social_url' => 'socials.Kanal resmi lainnya',
        ];

        foreach ($mapping as $dbKey => $path) {
            $value = SiteSetting::where('key', $dbKey)->value('value');
            if ($value === null || $value === '') {
                continue;
            }

            self::setValueByPath($merged, $path, $value);
        }

        $hoursRaw = SiteSetting::where('key', 'school_hours')->value('value');
        if (filled($hoursRaw)) {
            $hours = collect(preg_split('/\r\n|\r|\n/', $hoursRaw))
                ->map(function ($row) {
                    $parts = array_pad(explode('|', trim((string) $row), 2), 2, '');
                    if (! trim((string) $parts[0]) && ! trim((string) $parts[1])) {
                        return null;
                    }

                    return ['day' => trim((string) $parts[0]), 'time' => trim((string) $parts[1])];
                })
                ->filter()
                ->values()
                ->all();

            if ($hours !== []) {
                $merged['hours'] = $hours;
            }
        }

        return $merged;
    }

    protected static function setValueByPath(array &$target, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $current = &$target;

        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $current[$segment] = $value;
                return;
            }

            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }
    }
}
