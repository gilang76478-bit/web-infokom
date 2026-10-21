<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status terbit artikel: false = draf yang tidak pernah tampil di halaman
 * publik (beranda, indeks berita, detail, sitemap). Default true membuat
 * seluruh artikel yang sudah ada otomatis tetap terbit — tanpa perubahan
 * data dan tanpa menyentuh baris lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('is_published')->default(true)->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('is_published');
        });
    }
};
