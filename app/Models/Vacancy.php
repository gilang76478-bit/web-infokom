<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Vacancy extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'link',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'date',
    ];

    /**
     * Bersihkan berkas poster lowongan dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim yang
     * menumpuk. Nilai berupa URL eksternal tidak disentuh.
     */
    protected static function booted(): void
    {
        static::deleted(function (Vacancy $vacancy): void {
            if (filled($vacancy->image_path) && ! preg_match('#^(https?:)?//#i', $vacancy->image_path)) {
                Storage::disk('public')->delete($vacancy->image_path);
            }
        });
    }

    /** Poster terbaru tampil lebih dahulu pada halaman /bkk. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('posted_at')->orderByDesc('id');
    }

    /**
     * URL poster siap pakai untuk atribut src. Hasil unggahan panel admin
     * disimpan relatif pada disk publik sehingga diubah menjadi URL
     * storage; nilai lama berupa URL eksternal dipakai apa adanya.
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
}
