<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Magang Jepang diperkaya dua hal sesuai contoh websmk_infokom:
 * 1. Setiap langkah tahapan dapat memiliki foto (japan_steps.photo_path),
 *    diunggah lewat panel admin.
 * 2. Reel Instagram resmi sekolah (japan_reels): tautan, judul,
 *    keterangan, dan poster opsional — seluruhnya dikelola lewat panel
 *    admin, seperti daftar reel pada karier.php contoh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('japan_steps', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('duration');
        });

        Schema::create('japan_reels', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('poster_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('japan_reels');

        Schema::table('japan_steps', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
