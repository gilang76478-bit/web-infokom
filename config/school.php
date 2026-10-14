<?php

return [
    'contact' => [
        'name' => env('SCHOOL_NAME', 'SMK INFOKOM'),
        'address' => [
            'Alamat lengkap' => env('SCHOOL_ADDRESS_LINE', ''),
            'RT/RW' => env('SCHOOL_ADDRESS_RTRW', ''),
            'Kelurahan/Desa' => env('SCHOOL_ADDRESS_VILLAGE', ''),
            'Kecamatan' => env('SCHOOL_ADDRESS_DISTRICT', ''),
            'Kota/Kabupaten' => env('SCHOOL_ADDRESS_CITY', ''),
            'Provinsi' => env('SCHOOL_ADDRESS_PROVINCE', ''),
            'Kode pos' => env('SCHOOL_ADDRESS_POSTAL_CODE', ''),
        ],
        'phones' => [
            'general' => env('SCHOOL_PHONE_GENERAL', ''),
            'administration' => env('SCHOOL_PHONE_ADMIN', ''),
            'ppdb' => env('SCHOOL_PHONE_PPDB', ''),
            'industry' => env('SCHOOL_PHONE_INDUSTRY', ''),
            'alumni' => env('SCHOOL_PHONE_ALUMNI', ''),
        ],
        'whatsapp' => [
            'general' => env('SCHOOL_WHATSAPP_GENERAL', ''),
            'ppdb' => env('SCHOOL_WHATSAPP_PPDB', ''),
        ],
        'emails' => [
            'general' => env('SCHOOL_EMAIL_GENERAL', ''),
            'ppdb' => env('SCHOOL_EMAIL_PPDB', ''),
            'administration' => env('SCHOOL_EMAIL_ADMIN', ''),
            'industry' => env('SCHOOL_EMAIL_INDUSTRY', ''),
            'alumni' => env('SCHOOL_EMAIL_ALUMNI', ''),
        ],
        'maps_url' => env('SCHOOL_MAPS_URL', ''),
        'socials' => [
            'Instagram' => env('SCHOOL_INSTAGRAM_URL', ''),
            'TikTok' => env('SCHOOL_TIKTOK_URL', ''),
            'Facebook' => env('SCHOOL_FACEBOOK_URL', ''),
            'YouTube' => env('SCHOOL_YOUTUBE_URL', ''),
            'Kanal resmi lainnya' => env('SCHOOL_OTHER_SOCIAL_URL', ''),
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
    ],
];
