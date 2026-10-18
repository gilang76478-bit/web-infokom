<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GaleriController extends Controller
{
    /**
     * Galeri publik (/galeri) dengan filter kategori lewat ?kategori={slug}
     * dan paginasi agar jumlah foto yang membesar tidak membebani halaman.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('galleryItems')
            ->ofType('galeri')
            ->get();

        $activeCategory = null;
        $query = GalleryItem::query()->with('category')->orderBy('id');

        if (filled($request->query('kategori'))) {
            $activeCategory = $categories->firstWhere('slug', $request->query('kategori'));
            abort_unless($activeCategory, 404);

            $query->where('category_id', $activeCategory->id);
        }

        return view('galeri.index', [
            // Filter kategori dibawa ke tautan halaman berikutnya.
            'items' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }
}
