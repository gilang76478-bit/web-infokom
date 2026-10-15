<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Mengisi data guru dari site.json. Aman dijalankan berulang kali:
 * data guru yang sudah ada tidak digandakan.
 */
class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        if (Teacher::query()->exists()) {
            return;
        }

        $data = json_decode(file_get_contents(database_path('seeders/data/site.json')), true);

        foreach ($data['teachers'] as $row) {
            $programShort = $row['program'] ?? null;
            unset($row['program']);

            Teacher::create([
                ...$row,
                'program_id' => $programShort
                    ? Program::where('short', $programShort)->value('id')
                    : null,
            ]);
        }
    }
}
