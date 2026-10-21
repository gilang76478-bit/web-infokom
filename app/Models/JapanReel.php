<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Reel Instagram resmi sekolah untuk program PKL & Magang Jepang.
 * Tautan, judul, keterangan, dan poster dikelola lewat panel admin
 * (modul "Reel Magang Jepang") dan ditampilkan pada halaman /jepang.
 */
class JapanReel extends Model
{
    protected $fillable = [
        'url',
        'title',
        'description',
        'poster_path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Bersihkan berkas poster reel dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim.
     */
    protected static function booted(): void
    {
        static::deleted(function (JapanReel $reel): void {
            if (filled($reel->poster_path) && ! preg_match('#^(https?:)?//#i', $reel->poster_path)) {
                Storage::disk('public')->delete($reel->poster_path);
            }
        });
    }

    /** Urutan tampil pada bagian dokumentasi halaman /jepang. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * URL poster siap pakai. Hasil unggahan panel admin disimpan relatif
     * pada disk publik; nilai berupa URL eksternal dipakai apa adanya.
     */
    public function getPosterUrlAttribute(): ?string
    {
        if (blank($this->poster_path)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->poster_path) || str_starts_with($this->poster_path, '/')) {
            return $this->poster_path;
        }

        return asset('storage/'.ltrim($this->poster_path, '/'));
    }

    /** Kode unik reel Instagram dari tautan (untuk pratinjau/analitik). */
    public function getCodeAttribute(): string
    {
        preg_match('~/(?:reel|reels|p)/([A-Za-z0-9_-]+)~', (string) $this->url, $matches);

        return $matches[1] ?? '';
    }
}
