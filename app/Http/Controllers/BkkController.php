<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Vacancy;
use App\Support\SchoolSettings;

/**
 * Halaman Bursa Kerja Khusus (BKK): informasi lowongan berupa poster
 * (dikelola lewat panel admin, modul "Lowongan BKK") dan berita
 * penempatan alumni dari kategori BKK pada modul Berita.
 */
class BkkController extends Controller
{
    public function index()
    {
        return view('bkk.index', [
            'vacancies' => Vacancy::ordered()->get(),
            'posts' => Post::published()
                ->with('category')
                ->whereHas('category', fn ($query) => $query->where('slug', 'bkk'))
                ->orderByDesc('published_at')
                ->get(),
            'contact' => SchoolSettings::contact(),
        ]);
    }
}
