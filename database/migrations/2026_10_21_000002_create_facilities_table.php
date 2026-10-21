<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fasilitas sekolah kini menjadi modul konten tersendiri (bukan baris
 * teks pada pengaturan), sehingga setiap fasilitas dapat memiliki foto
 * HD dan dikelola lengkap lewat panel admin: judul, deskripsi, foto,
 * dan urutan tampil pada halaman /profil #fasilitas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
