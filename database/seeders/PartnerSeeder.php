<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi data mitra kerja sama dari partners.json: 14 logo HD
 * (9 dunia usaha & industri, 5 perguruan tinggi) plus 2 mitra lama
 * yang sudah terverifikasi. Logo disalin dari
 * database/seeders/data/photos/partners ke disk publik agar halaman
 * /industri dan beranda langsung menampilkan logo jernih.
 *
 * Aman dijalankan berulang kali: daftar mitra ditulis ulang sesuai
 * sumber data (tanpa duplikat), dan berkas logo tidak ditimpa.
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/data/partners.json')),
            true,
        );

        if (! is_array($data)) {
            return;
        }

        $sourceDir = database_path('seeders/data/photos/partners');
        $targetDir = 'photos/partners';

        File::ensureDirectoryExists(storage_path('app/public/'.$targetDir));

        foreach ($data as $row) {
            $file = $row['logo_file'] ?? null;

            if (blank($file)) {
                continue;
            }

            $target = $targetDir.'/'.$file;

            if (Storage::disk('public')->exists($target)) {
                continue;
            }

            $source = $sourceDir.'/'.$file;

            if (is_file($source)) {
                Storage::disk('public')->put($target, File::get($source));
            }
        }

        // Tulis ulang daftar mitra agar selalu sama dengan sumber data
        // (jumlah dan nama dapat berubah antar rilis konten sekolah).
        Partner::query()->delete();

        foreach ($data as $row) {
            $file = $row['logo_file'] ?? null;
            unset($row['logo_file']);

            Partner::create([
                ...$row,
                'logo' => filled($file) && Storage::disk('public')->exists($targetDir.'/'.$file)
                    ? $targetDir.'/'.$file
                    : null,
            ]);
        }
    }
}
