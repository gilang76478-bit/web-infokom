<?php

namespace Database\Seeders;

use App\Models\{Category, Program, Post, Agenda, Achievement, GalleryItem, JapanStep, Partner, Alumnus, TracerStat, Announcement};
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/site.json')), true);

        $map = [
            'programs'      => Program::class,
            'agendas'       => Agenda::class,
            'achievements'  => Achievement::class,
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

        // Foto galeri: string kategori dipetakan ke tabel categories
        // berjenis galeri (slug diisi eksplisit karena tanpa model events).
        foreach ($data['gallery_items'] as $row) {
            $categoryName = $row['category'] ?? null;
            unset($row['category']);

            GalleryItem::create([
                ...$row,
                'category_id' => filled($categoryName)
                    ? Category::firstOrCreate(
                        ['type' => 'galeri', 'slug' => Str::slug($categoryName) ?: 'kategori'],
                        ['name' => $categoryName],
                    )->id
                    : null,
            ]);
        }

        // Artikel: string kategori di site.json dipetakan ke tabel categories,
        // dan slug diisi eksplisit karena seeder berjalan tanpa model events.
        foreach ($data['posts'] as $row) {
            $categoryName = $row['category'] ?? null;
            unset($row['category']);

            Post::create([
                ...$row,
                'slug' => Post::makeUniqueSlug($row['title']),
                'category_id' => filled($categoryName)
                    ? Category::firstOrCreate(
                        ['type' => 'berita', 'slug' => Str::slug($categoryName) ?: 'kategori'],
                        ['name' => $categoryName],
                    )->id
                    : null,
            ]);
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