<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Poster lowongan Bursa Kerja Khusus (BKK): halaman /bkk menampilkan
 * poster lowongan terbaru dari tabel ini, sehingga sekolah dapat
 * menambah atau mengganti poster lewat panel admin tanpa menyentuh kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('link', 500)->nullable();
            $table->date('posted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
