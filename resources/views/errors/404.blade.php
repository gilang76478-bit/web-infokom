@extends('layouts.site', [
    'title' => 'Halaman tidak ditemukan | SMK INFOKOM',
    'description' => 'Halaman yang Anda tuju tidak ditemukan pada website resmi SMK INFOKOM.',
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">ERROR 404</p>
      <h1>Halaman tidak ditemukan</h1>
      <p>Halaman yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat yang diketik tidak lengkap. Silakan gunakan salah satu tautan berikut untuk melanjutkan.</p>
      <nav class="profile-hero-nav" aria-label="Navigasi pemulihan">
        <a href="{{ route('home') }}"><span aria-hidden="true">⌂</span> Kembali ke beranda</a>
        <a href="{{ route('site.section', 'profil') }}"><span aria-hidden="true">↓</span> Profil sekolah</a>
        <a href="{{ route('site.section', 'kontak') }}"><span aria-hidden="true">→</span> Hubungi kami</a>
      </nav>
    </div>
  </section>
@endsection
