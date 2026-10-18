<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Daftar berita (/berita) dengan filter kategori lewat ?kategori={slug}
     * dan paginasi agar halaman tetap ringan ketika artikel bertambah.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('posts')
            ->ofType(Category::TYPES[0])
            ->get();

        $activeCategory = null;
        $query = Post::query()->with('category')->orderByDesc('published_at');

        if (filled($request->query('kategori'))) {
            $activeCategory = $categories->firstWhere('slug', $request->query('kategori'));
            abort_unless($activeCategory, 404);

            $query->where('category_id', $activeCategory->id);
        }

        return view('berita.index', [
            // Filter kategori dibawa ke tautan halaman berikutnya.
            'posts' => $query->paginate(9)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    /**
     * Detail artikel (/berita/{post:slug}) dengan pengikatan slug eksplisit.
     */
    public function show(Post $post): View
    {
        $post->load('category');

        return view('berita.show', compact('post'));
    }
}
