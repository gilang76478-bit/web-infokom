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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug', 160)->nullable()->after('title');
            $table->longText('body')->nullable()->after('excerpt');
            $table->foreignId('category_id')->nullable()->after('category')
                ->constrained('categories')->nullOnDelete();
        });

        // 1. Angkat string bebas "category" yang sudah ada menjadi baris
        //    di tabel categories (semuanya berjenis berita).
        $categoryIds = [];
        $takenSlugs = [];
        foreach (DB::table('posts')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category') as $name) {
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
                'type' => 'berita',
            ]);
        }

        // 2. Isi slug dari judul, body dari ringkasan lama, dan kaitkan
        //    category_id setiap artikel ke kategorinya.
        $takenPostSlugs = [];
        foreach (DB::table('posts')->orderBy('id')->get() as $post) {
            $base = Str::slug($post->title) ?: 'berita-' . $post->id;
            $slug = $base;
            $suffix = 2;
            while (isset($takenPostSlugs[$slug])) {
                $slug = $base . '-' . $suffix++;
            }
            $takenPostSlugs[$slug] = true;

            DB::table('posts')->where('id', $post->id)->update([
                'slug' => $slug,
                'body' => $post->body ?? $post->excerpt,
                'category_id' => $categoryIds[$post->category] ?? null,
            ]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->unique('slug');
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'body']);
            $table->string('category', 30)->nullable()->after('title');
        });

        // Kembalikan nama kategori lama dari tabel categories.
        foreach (DB::table('posts')->get() as $post) {
            $name = $post->category_id
                ? DB::table('categories')->where('id', $post->category_id)->value('name')
                : null;

            DB::table('posts')->where('id', $post->id)->update(['category' => $name]);
        }
    }
};
