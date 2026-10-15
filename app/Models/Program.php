<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Program extends Model
{
    protected $fillable = [
        'short',
        'slug',
        'icon',
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
