@extends('layouts.site', [
    'activeSection' => 'informasi',
    'title' => 'Berita & Artikel | SMK INFOKOM',
    'description' => 'Kabar terbaru sekolah, prestasi siswa, kegiatan, dan informasi akademik dari SMK INFOKOM Bogor.',
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Informasi</p>
      <h1>Berita &amp; Artikel</h1>
      <p>Kabar terbaru sekolah, prestasi siswa, kegiatan, dan informasi akademik dalam satu halaman.</p>
      <nav class="berita-filters" aria-label="Filter kategori berita">
        <a href="{{ route('berita.index') }}"
           @class(['is-active' => $activeCategory === null])
           @if ($activeCategory === null) aria-current="page" @endif>Semua</a>
        @foreach ($categories as $category)
          <a href="{{ route('berita.index', ['kategori' => $category->slug]) }}"
             @class(['is-active' => $activeCategory?->slug === $category->slug])
             @if ($activeCategory?->slug === $category->slug) aria-current="page" @endif>
            {{ $category->name }} <span>{{ $category->posts_count }}</span>
          </a>
        @endforeach
      </nav>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <div class="detail-grid berita-grid">
        @forelse ($posts as $post)
          <article class="detail-card berita-card">
            <span class="detail-tag">{{ $post->category?->name ?? 'Berita' }}</span>
            <h2><a href="{{ route('berita.show', $post) }}">{{ $post->title }}</a></h2>
            <p>{{ $post->excerpt }}</p>
            <div class="berita-meta">
              <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</time>
              <a href="{{ route('berita.show', $post) }}">Baca selengkapnya <span aria-hidden="true">→</span></a>
            </div>
          </article>
        @empty
          <p class="detail-empty">Belum ada berita yang dipublikasikan pada kategori ini.</p>
        @endforelse
      </div>
    </div>
  </section>
@endsection
