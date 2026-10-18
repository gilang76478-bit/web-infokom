<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Mengisi tabel site_settings dengan data resmi sekolah dari
 * database/seeders/data/school.json (identitas, kontak, visi & misi,
 * serta riwayat sekolah pada halaman /profil).
 *
 * Aman dijalankan berulang kali: key yang sudah ada tidak ditimpa, sehingga
 * perubahan yang dilakukan admin melalui panel pengaturan tidak pernah hilang.
 */
class SchoolSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = json_decode(file_get_contents(database_path('seeders/data/school.json')), true, 512, JSON_THROW_ON_ERROR);

        $created = 0;
        $kept = 0;

        foreach ($settings as $key => $value) {
            if (blank($value)) {
                continue;
            }

            $setting = SiteSetting::firstOrCreate(
                ['key' => $key],
                ['value' => (string) $value],
            );

            $setting->wasRecentlyCreated ? $created++ : $kept++;
        }

        $this->command?->info("Pengaturan sekolah: {$created} key diisi, {$kept} key sudah ada dan dibiarkan.");
    }
}
