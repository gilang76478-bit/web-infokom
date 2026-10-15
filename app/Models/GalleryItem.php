<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'size',
        'art',
        'image_path',
    ];

    protected static function booted(): void
    {
        // Kolom size NOT NULL dengan default '' — normalisasi null dari
        // form (middleware mengubah string kosong menjadi null).
        static::saving(function (GalleryItem $item): void {
            if ($item->size === null) {
                $item->size = '';
            }
        });

        // Bersihkan file foto dari disk publik ketika record dihapus,
        // agar tidak ada file yatim tertinggal di storage.
        static::deleted(function (GalleryItem $item): void {
            if (filled($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** URL foto hasil unggahan pada disk publik; null bila belum ada foto. */
    public function getUrlAttribute(): ?string
    {
        // asset() mengikuti host permintaan berjalan, sehingga tautan foto
        // selalu benar di lingkungan lokal maupun produksi.
        return filled($this->image_path)
            ? asset('storage/' . $this->image_path)
            : null;
    }
}
