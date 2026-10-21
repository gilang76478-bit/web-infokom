<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'category',
        'logo',
    ];

    /**
     * Bersihkan berkas logo mitra dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim yang
     * menumpuk. Nilai berupa URL eksternal tidak disentuh.
     */
    protected static function booted(): void
    {
        static::deleted(function (Partner $partner): void {
            if (filled($partner->logo) && ! preg_match('#^(https?:)?//#i', $partner->logo)) {
                Storage::disk('public')->delete($partner->logo);
            }
        });
    }

    /**
     * URL logo siap pakai untuk atribut src. Hasil unggahan panel admin
     * disimpan relatif pada disk publik sehingga diubah menjadi URL
     * storage; nilai lama berupa URL eksternal dipakai apa adanya.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->logo) || str_starts_with($this->logo, '/')) {
            return $this->logo;
        }

        return asset('storage/'.ltrim($this->logo, '/'));
    }
}
