<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'art',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected static function booted(): void
    {
        // Slug dibuat sekali saat artikel pertama kali disimpan agar
        // tautan /berita/{slug} tetap stabil meski judul diubah.
        static::saving(function (Post $post): void {
            if (blank($post->slug)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }
        });
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
