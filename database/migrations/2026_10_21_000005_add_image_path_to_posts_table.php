<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto artikel berita: kolom image_path menyimpan berkas hasil unggahan
 * admin pada disk publik (mis. photos/posts/upacara.jpg). Sebelum kolom
 * ini terisi, tampilan daftar berita tetap memakai ilustrasi gradien
 * (kolom art) sebagai cadangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('art');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
