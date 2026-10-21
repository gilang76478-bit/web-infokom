<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Facility extends Model
{
    protected $fillable = [
        'title',
        'description',
        'photo_path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Bersihkan berkas foto fasilitas dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim yang
     * menumpuk. Nilai berupa URL eksternal tidak disentuh.
     */
    protected static function booted(): void
    {
        static::deleted(function (Facility $facility): void {
            if (filled($facility->photo_path) && ! preg_match('#^(https?:)?//#i', $facility->photo_path)) {
                Storage::disk('public')->delete($facility->photo_path);
            }
        });
    }

    /** Urutan tampil pada bagian fasilitas halaman /profil. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * URL foto siap pakai untuk atribut src. Hasil unggahan panel admin
     * disimpan relatif pada disk publik sehingga diubah menjadi URL
     * storage; nilai lama berupa URL eksternal dipakai apa adanya.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (blank($this->photo_path)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->photo_path) || str_starts_with($this->photo_path, '/')) {
            return $this->photo_path;
        }

        return asset('storage/'.ltrim($this->photo_path, '/'));
    }
}
