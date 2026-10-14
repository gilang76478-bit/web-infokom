<?php

namespace App\Http\Controllers;

use App\Models\{Achievement, Agenda, Alumnus, Announcement, ContactMessage, GalleryItem, JapanStep, Partner, Post, PpdbApplicant, Program, TracerStat, User};
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $counts = [
            ['Program keahlian', Program::count(), 'akademik'],
            ['Berita', Post::count(), 'berita'],
            ['Agenda', Agenda::count(), 'agenda'],
            ['Prestasi', Achievement::count(), 'prestasi'],
            ['Foto galeri', GalleryItem::count(), 'galeri'],
            ['Mitra industri', Partner::count(), 'industri'],
            ['Tahap Magang Jepang', JapanStep::count(), 'jepang'],
            ['Alumni', Alumnus::count(), 'alumni'],
            ['Statistik tracer', TracerStat::count(), 'tracer'],
            ['Pengumuman', Announcement::count(), 'pengumuman'],
            ['Pendaftar PPDB', PpdbApplicant::count(), 'ppdb'],
            ['Pesan masuk', ContactMessage::count(), 'messages'],
            ['Pengguna', User::count(), null],
        ];

        return view('admin.dashboard', compact('counts'));
    }
}