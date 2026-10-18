<?php

namespace App\Support;

/**
 * Pembentukan tautan WhatsApp (wa.me).
 *
 * Nomor disimpan apa adanya sesuai cara sekolah menuliskannya — termasuk
 * format lokal seperti "0878-7307-1400". wa.me memerlukan format
 * internasional tanpa tanda "+", sehingga awalan 0 dikonversi menjadi 62
 * agar tautannya tetap bisa dibuka.
 */
class WhatsApp
{
    /**
     * Hanya angka, dengan kode negara Indonesia bila nomor masih format lokal.
     */
    public static function number(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    /**
     * Tautan wa.me, dengan pesan pembuka opsional. Null bila nomor kosong.
     */
    public static function link(?string $number, ?string $message = null): ?string
    {
        if (blank($number)) {
            return null;
        }

        $url = 'https://wa.me/'.self::number($number);

        return filled($message) ? $url.'?'.http_build_query(['text' => $message]) : $url;
    }
}
