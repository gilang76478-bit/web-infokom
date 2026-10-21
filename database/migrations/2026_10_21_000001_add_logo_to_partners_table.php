<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Logo mitra (jalur relatif pada disk publik, diisi lewat panel
            // admin). Nullable karena mitra lama dapat tanpa logo.
            $table->string('logo')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
