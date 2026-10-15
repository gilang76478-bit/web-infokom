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
        Schema::table('programs', function (Blueprint $table) {
            $table->string('slug', 120)->after('short')->nullable();
        });

        // Backfill slug untuk data yang sudah ada (mis. setelah deploy).
        $taken = [];
        foreach (DB::table('programs')->orderBy('id')->get() as $program) {
            $base = Str::slug($program->name) ?: Str::slug($program->short) ?: 'program-' . $program->id;
            $slug = $base;
            $suffix = 2;
            while (in_array($slug, $taken, true)) {
                $slug = $base . '-' . $suffix++;
            }
            $taken[] = $slug;

            DB::table('programs')->where('id', $program->id)->update(['slug' => $slug]);
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
