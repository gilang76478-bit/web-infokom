<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Daftar berita (/berita): pencarian judul/isi, filter kategori &
     * tahun, berita utama, dan paginasi.
     *
     * - Hanya artikel terbit (scope published) yang disajikan.
     * - Semua filter ikut ke tautan halaman berikutnya lewat withQueryString.
     * - Berita utama = artikel terbit terbaru pada tampilan tanpa filter,
     *   dikeluarkan dari grid agar tidak tampil dua kali.
     * - Pencarian memakai parameter terikat (aman), bukan SQL mentah.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount(['posts as posts_count' => fn ($query) => $query->published()])
            ->ofType(Category::TYPES[0])
            ->get();

        $search = trim((string) $request->query('q', ''));
        $year = (string) $request->query('tahun', '');
        if (! ctype_digit($year)) {
            $year = '';
        }

        $activeCategory = null;
        if (filled($request->query('kategori'))) {
            $activeCategory = $categories->firstWhere('slug', $request->query('kategori'));
            abort_unless($activeCategory, 404);
        }

        $filtersActive = $activeCategory !== null || $search !== '' || $year !== '';

        $query = Post::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at');

        if ($activeCategory !== null) {
            $query->where('category_id', $activeCategory->id);
        }

        if ($search !== '') {
            $needle = '%'.$search.'%';
            $query->where(fn ($builder) => $builder
                ->where('title', 'like', $needle)
                ->orWhere('excerpt', 'like', $needle)
                ->orWhere('body', 'like', $needle));
        }

        if ($year !== '') {
            $query->whereYear('published_at', $year);
        }

        $featured = null;
        if (! $filtersActive) {
            $featured = (clone $query)->first();

            if ($featured !== null) {
                $query->whereKeyNot($featured->id);
            }
        }

        // Tahun yang benar-benar ada pada data — pilihan filter tidak mengarang.
        $years = Post::query()
            ->published()
            ->select('published_at')
            ->get()
            ->map(fn (Post $post) => $post->published_at->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('berita.index', [
            'posts' => $query->paginate(9)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'year' => $year,
            'years' => $years,
            'filtersActive' => $filtersActive,
            'featured' => $featured,
            'publishedTotal' => Post::query()->published()->count(),
        ]);
    }

    /**
     * Detail artikel (/berita/{post:slug}) dengan pengikatan slug eksplisit.
     * Draf admin dibalas 404; berita terkait diurutkan: kategori sama dulu,
     * lalu yang terbaru, maksimal tiga artikel lain.
     */
    public function show(Post $post): View
    {
        abort_unless($post->is_published, 404);

        $post->load('category');

        $related = Post::query()
            ->published()
            ->with('category')
            ->whereKeyNot($post->id)
            ->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$post->category_id])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('berita.show', compact('post', 'related'));
    }
}
