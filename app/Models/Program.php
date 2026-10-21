<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Program extends Model
{
    protected $fillable = [
        'short',
        'slug',
        'icon',
        'photo_path',
        'name',
        'description',
        'skills',
        'jobs',
        'facilities',
    ];

    protected $casts = [
        'skills' => 'array',
        'jobs' => 'array',
        'facilities' => 'array',
    ];

    protected static function booted(): void
    {
        // Slug dibuat sekali saat program pertama kali disimpan agar
        // tautan /program/{slug} tetap stabil meski nama program diubah.
        static::saving(function (Program $program): void {
            if (blank($program->slug)) {
                $program->slug = static::makeUniqueSlug($program->name, $program->short);
            }
        });

        // Foto jurusan ikut terhapus dari disk publik saat catutan dihapus
        // agar tidak ada berkas yatim. Penggantian/penghapusan foto saat
        // edit ditangani AdminContentController (field bertipe berkas).
        static::deleted(function (Program $program): void {
            if (filled($program->photo_path) && ! preg_match('#^(https?:)?//#i', $program->photo_path)) {
                Storage::disk('public')->delete($program->photo_path);
            }
        });
    }

    /**
     * URL tampilan foto jurusan. Nilai URL eksternal dipakai apa adanya;
     * berkas hasil unggahan direlasikan terhadap disk publik.
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

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Membuat slug unik dari nama program (fallback: singkatan).
     * Dipakai oleh event model maupun oleh seeder, yang berjalan
     * dengan model events dimatikan oleh DatabaseSeeder.
     */
    public static function makeUniqueSlug(string $name, string $short): string
    {
        $base = Str::slug($name) ?: Str::slug($short) ?: 'program';

        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
