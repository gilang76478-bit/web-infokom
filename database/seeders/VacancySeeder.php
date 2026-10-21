<?php

namespace Database\Seeders;

use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi poster lowongan BKK dari vacancies.json (sumber: poster
 * "Bursa-lowongankerja.jpg" yang diberikan sekolah). Poster disalin dari
 * database/seeders/data/photos/vacancies ke disk publik agar halaman
 * /bkk langsung menampilkan poster jernih.
 *
 * Aman dijalankan berulang kali: daftar lowongan ditulis ulang sesuai
 * sumber data (tanpa duplikat), dan berkas poster tidak ditimpa.
 */
class VacancySeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/data/vacancies.json')),
            true,
        );

        if (! is_array($data)) {
            return;
        }

        $sourceDir = database_path('seeders/data/photos/vacancies');
        $targetDir = 'photos/vacancies';

        File::ensureDirectoryExists(storage_path('app/public/'.$targetDir));

        foreach ($data as $row) {
            $file = $row['image_file'] ?? null;

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

        // Tulis ulang daftar lowongan agar selalu sama dengan sumber data;
        // query()->delete() sengaja dipakai agar event model tidak
        // menghapus berkas poster yang baru disalin di atas.
        Vacancy::query()->delete();

        foreach ($data as $row) {
            $file = $row['image_file'] ?? null;
            unset($row['image_file']);

            Vacancy::create([
                ...$row,
                'image_path' => filled($file) && Storage::disk('public')->exists($targetDir.'/'.$file)
                    ? $targetDir.'/'.$file
                    : null,
            ]);
        }
    }
}
