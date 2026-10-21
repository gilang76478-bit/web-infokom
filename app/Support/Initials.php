<?php

namespace App\Support;

/**
 * Inisial nama untuk placeholder foto ketika foto belum diunggah.
 *
 * Dua kata pertama dipakai sebagai inisial, tetapi gelar dibuang lebih dulu:
 * token bergaris miring (Ir., M.MPd) dan gelar umum (Hj, Drs, Prof) tidak
 * boleh ikut menghasilkan inisial — inisial harus berasal dari nama orangnya.
 */
class Initials
{
    /**
     * Gelar umum yang dibuang (huruf kecil, pencocokan tanpa memedulikan besar-kecil).
     *
     * @var array<int, string>
     */
    private const TITLES = ['ir', 'hj', 'dr', 'drs', 'dra', 'h', 'prof', 'mr', 'mrs', 'ms'];

    public static function from(string $name, string $fallback = 'G'): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->map(fn (string $word) => trim($word, ',.'))
            ->filter(fn (string $word) => $word !== ''
                && ! str_contains($word, '.')
                && ! in_array(mb_strtolower($word), self::TITLES, true))
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : $fallback;
    }
}
