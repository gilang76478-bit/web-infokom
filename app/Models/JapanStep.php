<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class JapanStep extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration',
        'photo_path',
    ];

    /**
     * Bersihkan berkas foto langkah dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim.
     * Nilai berupa URL eksternal tidak disentuh.
     */
    protected static function booted(): void
    {
        static::deleted(function (JapanStep $step): void {
            if (filled($step->photo_path) && ! preg_match('#^(https?:)?//#i', $step->photo_path)) {
                Storage::disk('public')->delete($step->photo_path);
            }
        });
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
