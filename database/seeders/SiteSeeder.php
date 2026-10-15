<?php

namespace Database\Seeders;

use App\Models\{Program, Post, Agenda, Achievement, GalleryItem, JapanStep, Partner, Alumnus, TracerStat, Announcement};
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/site.json')), true);

        $map = [
            'programs'      => Program::class,
            'posts'         => Post::class,
            'agendas'       => Agenda::class,
            'achievements'  => Achievement::class,
            'gallery_items' => GalleryItem::class,
            'japan_steps'   => JapanStep::class,
            'partners'      => Partner::class,
            'alumni'        => Alumnus::class,
            'tracer_stats'  => TracerStat::class,
            'announcements' => Announcement::class,
        ];

        foreach ($map as $key => $model) {
            foreach ($data[$key] as $row) {
                $model::create($row);
            }
        }

        // Seeder berjalan dengan model events dimatikan (DatabaseSeeder),
        // sehingga pembuatan slug pada event model tidak berjalan —
        // slug program diisi secara eksplisit di sini.
        foreach (Program::query()->whereNull('slug')->orderBy('id')->get() as $program) {
            $program->forceFill([
                'slug' => Program::makeUniqueSlug($program->name, $program->short),
            ])->save();
        }

        $this->call(TeacherSeeder::class);
    }
}