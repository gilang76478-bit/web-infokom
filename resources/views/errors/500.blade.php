@extends('layouts.site', [
    'title' => 'Terjadi kesalahan server | SMK INFOKOM',
    'description' => 'Terjadi kesalahan pada server website resmi SMK INFOKOM. Tim kami sedang menangani masalah ini.',
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">ERROR 500</p>
      <h1>Terjadi kesalahan di server kami</h1>
      <p>Tim teknis kami sudah diberi tahu dan sedang menangani masalah ini. Silakan coba lagi dalam beberapa saat, atau kembali ke beranda untuk melanjutkan menjelajahi website.</p>
      <nav class="profile-hero-nav" aria-label="Navigasi pemulihan">
        <a href="{{ route('home') }}"><span aria-hidden="true">⌂</span> Kembali ke beranda</a>
        <a href="{{ route('site.section', 'kontak') }}"><span aria-hidden="true">→</span> Hubungi kami</a>
      </nav>
    </div>
  </section>
@endsection
