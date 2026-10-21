<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi 13 fasilitas sekolah dari facilities.json (sumber: foto
 * "Foto-foto Infokom/Fasilitas" yang diberikan sekolah). Foto disalin
 * dari database/seeders/data/photos/facilities ke disk publik agar
 * bagian fasilitas pada /profil langsung menampilkan foto jernih.
 *
 * Aman dijalankan berulang kali: daftar fasilitas ditulis ulang sesuai
 * sumber data (tanpa duplikat), dan berkas foto tidak ditimpa.
 */
class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/data/facilities.json')),
            true,
        );

        if (! is_array($data)) {
            return;
        }

        $sourceDir = database_path('seeders/data/photos/facilities');
        $targetDir = 'photos/facilities';

        File::ensureDirectoryExists(storage_path('app/public/'.$targetDir));

        foreach ($data as $row) {
            $file = $row['photo_file'] ?? null;

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

        // Tulis ulang daftar fasilitas agar selalu sama dengan sumber
        // data; query()->delete() sengaja dipakai agar event model tidak
        // menghapus berkas foto yang baru disalin di atas.
        Facility::query()->delete();

        foreach ($data as $row) {
            $file = $row['photo_file'] ?? null;
            unset($row['photo_file']);

            Facility::create([
                ...$row,
                'photo_path' => filled($file) && Storage::disk('public')->exists($targetDir.'/'.$file)
                    ? $targetDir.'/'.$file
                    : null,
            ]);
        }
    }
}
