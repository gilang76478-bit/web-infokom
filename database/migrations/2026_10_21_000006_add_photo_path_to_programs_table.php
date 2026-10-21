<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto program keahlian (jurusan): kolom photo_path menyimpan berkas
 * hasil unggahan admin pada disk publik (mis. photos/programs/tkj.jpg).
 * Dipakai kartu jurusan berfoto pada halaman /akademik dan halaman
 * detail program; tanpa foto, kartu kembali ke tampilan teks biasa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
