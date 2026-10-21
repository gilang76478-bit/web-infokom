<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'nip',
        'position',
        'photo',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Bersihkan berkas foto guru dari disk publik ketika catutannya
     * dihapus lewat panel admin, supaya tidak ada berkas yatim yang
     * menumpuk. Nilai berupa URL eksternal tidak disentuh.
     */
    protected static function booted(): void
    {
        static::deleted(function (Teacher $teacher): void {
            if (filled($teacher->photo) && ! preg_match('#^(https?:)?//#i', $teacher->photo)) {
                Storage::disk('public')->delete($teacher->photo);
            }
        });
    }

    /** Urutan tampil di halaman profil dan halaman detail program. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * URL foto siap pakai untuk atribut src. Hasil unggahan panel admin
     * disimpan relatif pada disk publik sehingga diubah menjadi URL
     * storage; nilai lama berupa URL eksternal (https://...) dipakai
     * apa adanya agar data lawas tetap tampil.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (blank($this->photo)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->photo) || str_starts_with($this->photo, '/')) {
            return $this->photo;
        }

        return asset('storage/'.ltrim($this->photo, '/'));
    }

    /** Inisial nama untuk placeholder foto ketika foto belum diunggah. */
    public function getInitialsAttribute(): string
    {
        return \App\Support\Initials::from($this->name, 'G');
    }
}
