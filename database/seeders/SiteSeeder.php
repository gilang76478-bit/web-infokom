<?php

namespace Database\Seeders;

use App\Models\{Category, Program, Post, Agenda, Achievement, GalleryItem, JapanReel, JapanStep, Alumnus, TracerStat, Announcement};
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/site.json')), true);

        // Foto per baris (artikel berita, foto galeri, foto jurusan): berkas
        // sumber berada di database/seeders/data/photos/<grup> dan disalin ke
        // disk publik sebelum baris dibuat — dilewati bila target sudah ada
        // agar unggahan admin yang lebih baru tidak tertimpa.
        $photoGroups = [
            'posts' => 'posts',
            'gallery_items' => 'gallery',
            'programs' => 'programs',
        ];

        foreach ($photoGroups as $key => $sourceGroup) {
            foreach ($data[$key] as $row) {
                $path = $row['image_path'] ?? $row['photo_path'] ?? null;

                if (blank($path) || Storage::disk('public')->exists($path)) {
                    continue;
                }

                $source = database_path('seeders/data/photos/'.$sourceGroup.'/'.basename($path));

                if (is_file($source)) {
                    Storage::disk('public')->put($path, File::get($source));
                }
            }
        }

        // Mitra kerja sama memakai seeder sendiri (PartnerSeeder) karena
        // perlu menyalin 14 logo HD ke disk publik sebelum baris dibuat.
        $map = [
            'programs'      => Program::class,
            'agendas'       => Agenda::class,
            'achievements'  => Achievement::class,
            'japan_steps'   => JapanStep::class,
            'japan_reels'   => JapanReel::class,
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
        $this->call(PartnerSeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(VacancySeeder::class);
    }
}