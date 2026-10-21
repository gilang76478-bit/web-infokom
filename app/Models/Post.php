<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'category_id',
        'slug',
        'title',
        'excerpt',
        'body',
        'published_at',
        'is_published',
        'art',
        'image_path',
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_published' => 'boolean',
    ];

    /**
     * Hanya artikel terbit yang boleh tampil di halaman publik;
     * draf admin dikecualikan dari daftar, detail, beranda, dan sitemap.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    protected static function booted(): void
    {
        // Slug dibuat sekali saat artikel pertama kali disimpan agar
        // tautan /berita/{slug} tetap stabil meski judul diubah.
        static::saving(function (Post $post): void {
            if (blank($post->slug)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }
        });

        // Foto artikel ikut terhapus dari disk publik saat catutan dihapus
        // agar tidak ada berkas yatim. Penggantian foto saat edit juga
        // dibersihkan di sini sehingga berkas lama tidak menumpuk walau
        // field unggahan Filament menyimpan unggahan baru lebih dulu.
        static::updating(function (Post $post): void {
            $previous = $post->getOriginal('image_path');

            if ($post->isDirty('image_path')
                && filled($previous)
                && ! preg_match('#^(https?:)?//#i', (string) $previous)) {
                Storage::disk('public')->delete($previous);
            }
        });

        static::deleted(function (Post $post): void {
            if (filled($post->image_path) && ! preg_match('#^(https?:)?//#i', $post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
            }
        });
    }

    /**
     * URL tampilan foto artikel. Nilai URL eksternal dipakai apa adanya;
     * berkas hasil unggahan direlasikan terhadap disk publik.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->image_path) || str_starts_with($this->image_path, '/')) {
            return $this->image_path;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Slug unik dari judul artikel (dipakai oleh event model maupun seeder,
     * yang berjalan dengan model events dimatikan oleh DatabaseSeeder).
     */
    public static function makeUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'berita';

        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
