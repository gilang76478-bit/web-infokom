<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi data 32 guru dan tenaga kependidikan dari teachers.json
 * (sumber: SQL contoh portal v3). Foto disalin dari
 * database/seeders/data/photos/teachers ke disk publik agar halaman
 * profil, /guru, dan detail program langsung menampilkan wajah guru.
 *
 * Aman dijalankan berulang kali: data guru yang sudah ada tidak
 * digandakan, dan berkas foto yang sudah tersalin tidak ditimpa.
 */
class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/data/teachers.json')),
            true,
        );

        if (! is_array($data)) {
            return;
        }

        $sourceDir = database_path('seeders/data/photos/teachers');
        $targetDir = 'photos/teachers';

        // Salin berkas foto satu per satu; dilewati bila sudah ada agar
        // penebaran ulang tidak menimpa unggahan admin yang lebih baru.
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

        // Guru contoh dihapus agar penebaran ulang mengikuti daftar resmi
        // terbaru (jumlah dan nama dapat berubah antar angkatan data).
        Teacher::query()->delete();

        foreach ($data as $row) {
            $programShort = $row['program'] ?? null;
            $file = $row['photo_file'] ?? null;
            unset($row['program'], $row['photo_file']);

            Teacher::create([
                ...$row,
                'program_id' => filled($programShort)
                    ? Program::where('short', $programShort)->value('id')
                    : null,
                'photo' => filled($file) && Storage::disk('public')->exists($targetDir.'/'.$file)
                    ? $targetDir.'/'.$file
                    : null,
            ]);
        }
    }
}
