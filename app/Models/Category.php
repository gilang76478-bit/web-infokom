<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** Jenis kategori yang didukung: pemisah berita, agenda, dan galeri. */
    public const TYPES = ['berita', 'agenda', 'galeri'];

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    protected static function booted(): void
    {
        // Slug dibuat sekali saat kategori disimpan agar tautan filter
        // /berita?kategori={slug} tetap stabil meski nama diubah.
        static::saving(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = static::makeUniqueSlug($category->name, $category->type);
            }
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type)->orderBy('name');
    }

    /**
     * Slug unik di dalam satu jenis kategori (kolom unik gabungan type+slug).
     */
    public static function makeUniqueSlug(string $name, string $type): string
    {
        $base = Str::slug($name) ?: 'kategori';

        $slug = $base;
        $suffix = 2;

        while (static::where('type', $type)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
