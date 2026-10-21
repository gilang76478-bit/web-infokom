<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\View\View;

class GuruController extends Controller
{
    /**
     * Direktori guru dan tenaga kependidikan (/guru).
     *
     * Pengelompokan murni berbasis jabatan yang ada pada data — tidak ada
     * nama atau peran yang dikarang:
     * - Pimpinan: jabatan "kepala sekolah" atau "wakil kepala" saja,
     *   sehingga kepala tata usaha dan serupa tidak salah masuk pimpinan.
     * - Tenaga kependidikan: jabatan administrasi/pendukung (tata usaha,
     *   operator, laboratorium, staf, dan sejenisnya).
     * - Selebihnya dianggap tenaga pendidik (guru).
     */
    public function index(): View
    {
        $teachers = Teacher::query()->with('program')->ordered()->get();

        $isLeader = fn (Teacher $teacher): bool => (bool) preg_match(
            '/(wakil\s+)?kepala\s+sekolah/i',
            $teacher->position,
        );

        $isSupport = fn (Teacher $teacher): bool => (bool) preg_match(
            '/tata\s?usaha|operator|administrator|\badmin\b|staf|staff|laboratorium|perpustakaan|teknisi|teknis/i',
            $teacher->position,
        );

        $leaders = $teachers->filter($isLeader)->values();
        $rest = $teachers->reject($isLeader)->values();

        return view('guru.index', [
            'leaders' => $leaders,
            'educators' => $rest->reject($isSupport)->values(),
            'support' => $rest->filter($isSupport)->values(),
        ]);
    }
}
