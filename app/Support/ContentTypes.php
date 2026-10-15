<?php

namespace App\Support;

use App\Models\{Achievement, Agenda, Alumnus, Announcement, GalleryItem, JapanStep, Partner, Post, Program, Teacher, TracerStat};

/**
 * Definisi modul konten admin: label, model, dan daftar field beserta
 * aturan validasinya. Dipakai bersama oleh AdminContentController (CRUD)
 * dan AdminContentRequest (validasi), sehingga aturan validasi tidak
 * lagi tersebar inline di dalam controller.
 */
class ContentTypes
{
    public const ALL = [
        'akademik' => ['label' => 'Program keahlian', 'model' => Program::class, 'fields' => [
            'short' => ['label' => 'Singkatan', 'type' => 'text', 'rules' => 'required|string|max:10'],
            'icon' => ['label' => 'Ikon', 'type' => 'text', 'rules' => 'required|in:code,network,pen,calc,brief'],
            'name' => ['label' => 'Nama program', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'required|string|max:4000'],
            'skills' => ['label' => 'Kompetensi (satu per baris)', 'type' => 'lines', 'rules' => 'required|string|max:4000'],
            'jobs' => ['label' => 'Prospek karier (satu per baris)', 'type' => 'lines', 'rules' => 'required|string|max:4000'],
            'facilities' => ['label' => 'Fasilitas (satu per baris)', 'type' => 'lines', 'rules' => 'required|string|max:4000'],
        ]],
        'guru' => ['label' => 'Guru & Tenaga Kependidikan', 'model' => Teacher::class, 'fields' => [
            'name' => ['label' => 'Nama guru', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'nip' => ['label' => 'NIP / NIDN', 'type' => 'text', 'rules' => 'nullable|string|max:30'],
            'position' => ['label' => 'Jabatan / peran', 'type' => 'text', 'rules' => 'required|string|max:120'],
            'program_id' => ['label' => 'Program keahlian', 'type' => 'select', 'options' => 'programs', 'rules' => 'nullable|integer|exists:programs,id'],
            'photo' => ['label' => 'URL foto', 'type' => 'text', 'rules' => 'nullable|string|max:500'],
            'sort_order' => ['label' => 'Urutan tampil', 'type' => 'number', 'rules' => 'required|integer|min:0|max:9999'],
        ]],
        'berita' => ['label' => 'Berita', 'model' => Post::class, 'fields' => [
            'category' => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'required|string|max:30'],
            'title' => ['label' => 'Judul', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'excerpt' => ['label' => 'Ringkasan', 'type' => 'textarea', 'rules' => 'required|string|max:4000'],
            'published_at' => ['label' => 'Tanggal terbit', 'type' => 'date', 'rules' => 'required|date'],
            'art' => ['label' => 'Variasi ilustrasi (0-5)', 'type' => 'number', 'rules' => 'required|integer|min:0|max:5'],
        ]],
        'agenda' => ['label' => 'Agenda', 'model' => Agenda::class, 'fields' => [
            'date' => ['label' => 'Tanggal', 'type' => 'date', 'rules' => 'required|date'],
            'title' => ['label' => 'Nama agenda', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'place_time' => ['label' => 'Waktu dan tempat', 'type' => 'text', 'rules' => 'required|string|max:255'],
        ]],
        'prestasi' => ['label' => 'Prestasi', 'model' => Achievement::class, 'fields' => [
            'title' => ['label' => 'Nama prestasi', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'who' => ['label' => 'Siswa atau tim', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'level' => ['label' => 'Tingkat', 'type' => 'text', 'rules' => 'required|string|max:30'],
            'year' => ['label' => 'Tahun', 'type' => 'number', 'rules' => 'required|integer|min:2000|max:2100'],
        ]],
        'galeri' => ['label' => 'Galeri', 'model' => GalleryItem::class, 'fields' => [
            'category' => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'required|string|max:30'],
            'title' => ['label' => 'Keterangan', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'size' => ['label' => 'Ukuran tile (kosong, w, atau t)', 'type' => 'text', 'rules' => 'nullable|in:w,t'],
            'art' => ['label' => 'Variasi ilustrasi (0-5)', 'type' => 'number', 'rules' => 'required|integer|min:0|max:5'],
        ]],
        'jepang' => ['label' => 'Tahap Magang Jepang', 'model' => JapanStep::class, 'fields' => [
            'title' => ['label' => 'Nama tahap', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'required|string|max:4000'],
            'duration' => ['label' => 'Durasi', 'type' => 'text', 'rules' => 'required|string|max:255'],
        ]],
        'industri' => ['label' => 'Mitra industri', 'model' => Partner::class, 'fields' => [
            'name' => ['label' => 'Nama mitra', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'category' => ['label' => 'Bidang', 'type' => 'text', 'rules' => 'required|string|max:30'],
        ]],
        'alumni' => ['label' => 'Cerita alumni', 'model' => Alumnus::class, 'fields' => [
            'name' => ['label' => 'Nama alumni', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'role_desc' => ['label' => 'Pekerjaan atau studi', 'type' => 'textarea', 'rules' => 'required|string|max:1000'],
            'quote' => ['label' => 'Cerita', 'type' => 'textarea', 'rules' => 'required|string|max:4000'],
        ]],
        'tracer' => ['label' => 'Statistik tracer', 'model' => TracerStat::class, 'fields' => [
            'label' => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'value' => ['label' => 'Persentase', 'type' => 'number', 'rules' => 'required|integer|min:0|max:100'],
            'color' => ['label' => 'Warna HEX', 'type' => 'text', 'rules' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']],
        ]],
        'pengumuman' => ['label' => 'Pengumuman', 'model' => Announcement::class, 'fields' => [
            'text' => ['label' => 'Isi pengumuman', 'type' => 'textarea', 'rules' => 'required|string|max:1000'],
        ]],
    ];

    /**
     * Opsi untuk field bertipe select, dipetakan dari sumber data
     * yang terdaftar di sini (bisa dipanggil dari view maupun request).
     *
     * @return array<int|string, string>
     */
    public static function options(string $source): array
    {
        return match ($source) {
            'programs' => Program::query()->orderBy('name')->pluck('name', 'id')->all(),
            default => [],
        };
    }

    /** Teks tampilan untuk nilai select pada tabel daftar admin. */
    public static function optionLabel(string $source, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return self::options($source)[$value] ?? (string) $value;
    }
}
