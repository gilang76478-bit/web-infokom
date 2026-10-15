<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')
                ->constrained('categories')->nullOnDelete();
            $table->string('image_path')->nullable()->after('art');
        });

        // 1. Angkat string kategori lama menjadi baris di tabel categories
        //    berjenis galeri (berbeda baris dengan kategori berita yang sejenis).
        $categoryIds = [];
        $takenSlugs = [];
        foreach (DB::table('gallery_items')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category') as $name) {
            $base = Str::slug($name) ?: 'kategori';
            $slug = $base;
            $suffix = 2;
            while (isset($takenSlugs[$slug])) {
                $slug = $base . '-' . $suffix++;
            }
            $takenSlugs[$slug] = true;

            $categoryIds[$name] = DB::table('categories')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'type' => 'galeri',
            ]);
        }

        // 2. Kaitkan tiap foto ke kategorinya, lalu buang kolom string lama
        //    agar kategori tidak lagi ganda sumber.
        foreach (DB::table('gallery_items')->get() as $item) {
            DB::table('gallery_items')->where('id', $item->id)->update([
                'category_id' => $categoryIds[$item->category] ?? null,
            ]);
        }

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('image_path');
            $table->string('category', 30)->nullable()->after('title');
        });

        foreach (DB::table('gallery_items')->get() as $item) {
            $name = $item->category_id
                ? DB::table('categories')->where('id', $item->category_id)->value('name')
                : null;

            DB::table('gallery_items')->where('id', $item->id)->update(['category' => $name]);
        }
    }
};
