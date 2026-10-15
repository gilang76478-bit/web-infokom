@extends('layouts.site', [
    'activeSection' => 'galeri',
    'title' => 'Galeri Kegiatan | SMK INFOKOM',
    'description' => 'Dokumentasi foto kegiatan belajar, praktik, dan prestasi siswa SMK INFOKOM Bogor.',
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Dokumentasi</p>
      <h1>Galeri Kegiatan</h1>
      <p>Cuplikan aktivitas belajar, berkarya, dan bertumbuh bersama di SMK INFOKOM.</p>
      <nav class="berita-filters" aria-label="Filter kategori galeri">
        <a href="{{ route('galeri.index') }}"
           @class(['is-active' => $activeCategory === null])
           @if ($activeCategory === null) aria-current="page" @endif>Semua <span>{{ $items->count() }}</span></a>
        @foreach ($categories as $category)
          <a href="{{ route('galeri.index', ['kategori' => $category->slug]) }}"
             @class(['is-active' => $activeCategory?->slug === $category->slug])
             @if ($activeCategory?->slug === $category->slug) aria-current="page" @endif>
            {{ $category->name }} <span>{{ $category->gallery_items_count }}</span>
          </a>
        @endforeach
      </nav>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      @if ($items->isEmpty())
        <p class="detail-empty">Belum ada foto yang dipublikasikan pada galeri ini.</p>
      @else
        <div class="galeri-grid">
          @foreach ($items as $item)
            <figure @class(['galeri-card', 'size-w' => $item->size === 'w'])>
              @if ($item->url)
                <img src="{{ $item->url }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
              @else
                <div class="galeri-placeholder galeri-art-{{ $item->art }}" role="img" aria-label="{{ $item->title }}"></div>
              @endif
              <figcaption>
                <span class="detail-tag">{{ $item->category?->name ?? 'Galeri' }}</span>
                <b>{{ $item->title }}</b>
              </figcaption>
            </figure>
          @endforeach
        </div>
      @endif
    </div>
  </section>
@endsection
