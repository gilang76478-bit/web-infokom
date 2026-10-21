@extends('layouts.site', [
    'activeSection' => 'informasi',
    'title' => 'Berita & Artikel | SMK INFOKOM',
    'description' => 'Kabar terbaru sekolah, prestasi siswa, kegiatan, dan informasi akademik dari SMK INFOKOM Bogor.',
])

@section('content')
  {{-- Remah navigasi Schema.org: Beranda → Berita & Artikel. --}}
  @push('jsonld')
    {!! \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumb('Berita & Artikel', route('berita.index'))) !!}
  @endpush

  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Informasi</p>
      <h1>Berita &amp; Artikel</h1>
      <p>Kabar terbaru sekolah, prestasi siswa, kegiatan, dan informasi akademik dalam satu halaman.</p>
      <p class="berita-total">{{ $publishedTotal }} artikel terbit</p>
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
      {{-- Pencarian teks + filter tahun. Kategori aktif ikut tersembunyi
           agar chip kategori tidak lepas saat pengguna mencari. --}}
      <form class="berita-search" method="get" action="{{ route('berita.index') }}" role="search">
        @if ($activeCategory !== null)
          <input type="hidden" name="kategori" value="{{ $activeCategory->slug }}">
        @endif
        <span class="berita-search-field">
          <label for="berita-q">Cari artikel</label>
          <input type="search" id="berita-q" name="q" value="{{ $search }}" placeholder="Kata kunci judul atau isi…">
        </span>
        @if ($years->count() > 1)
          <span class="berita-search-field">
            <label for="berita-tahun">Tahun</label>
            <select id="berita-tahun" name="tahun">
              <option value="">Semua tahun</option>
              @foreach ($years as $availableYear)
                <option value="{{ $availableYear }}" @selected((string) $availableYear === $year)>{{ $availableYear }}</option>
              @endforeach
            </select>
          </span>
        @endif
        <button class="btn btn-brand" type="submit">Tampilkan</button>
      </form>

      {{-- Status hasil aktif: jumlah nyata + jalan kembali. --}}
      @if ($filtersActive)
        <p class="berita-count" role="status">
          Menampilkan {{ $posts->total() }} artikel
          @if ($search !== '') untuk “{{ $search }}” @endif
          @if ($activeCategory !== null) pada kategori {{ $activeCategory->name }} @endif
          @if ($year !== '') tahun {{ $year }} @endif
          — <a class="link" href="{{ route('berita.index') }}">Tampilkan semua</a>
        </p>
      @endif

      {{-- Berita utama: terbit terbaru pada tampilan tanpa filter. --}}
      @if ($featured !== null)
        <article class="detail-card berita-featured">
          @if (filled($featured->image_url))
            <img class="berita-art berita-photo" src="{{ $featured->image_url }}" alt="{{ $featured->title }}" loading="lazy" decoding="async">
          @else
            <div class="berita-art berita-art-{{ $featured->art }}" aria-hidden="true"></div>
          @endif
          <div class="berita-featured-body">
            <span class="detail-tag">Berita utama · {{ $featured->category?->name ?? 'Berita' }}</span>
            <h2><a href="{{ route('berita.show', $featured) }}">{{ $featured->title }}</a></h2>
            <p>{{ $featured->excerpt }}</p>
            <div class="berita-meta">
              <time datetime="{{ $featured->published_at->toDateString() }}">{{ $featured->published_at->locale('id')->translatedFormat('j F Y') }}</time>
              <a href="{{ route('berita.show', $featured) }}">Baca selengkapnya <span aria-hidden="true">→</span></a>
            </div>
          </div>
        </article>
      @endif

      <div class="detail-grid berita-grid">
        @forelse ($posts as $post)
          <article class="detail-card berita-card">
            @if (filled($post->image_url))
              <img class="berita-art berita-photo" src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
            @else
              <div class="berita-art berita-art-{{ $post->art }}" aria-hidden="true"></div>
            @endif
            <span class="detail-tag">{{ $post->category?->name ?? 'Berita' }}</span>
            <h2><a href="{{ route('berita.show', $post) }}">{{ $post->title }}</a></h2>
            <p>{{ $post->excerpt }}</p>
            <div class="berita-meta">
              <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</time>
              <a href="{{ route('berita.show', $post) }}">Baca selengkapnya <span aria-hidden="true">→</span></a>
            </div>
          </article>
        @empty
          @if ($filtersActive)
            <p class="detail-empty">Tidak ada artikel yang cocok dengan pencarian atau filter saat ini. <a class="link" href="{{ route('berita.index') }}">Tampilkan semua artikel</a></p>
          @else
            <p class="detail-empty">Belum ada berita yang dipublikasikan pada kategori ini.</p>
          @endif
        @endforelse
      </div>

      @if ($posts->hasPages())
        <nav class="berita-filters list-pager" aria-label="Navigasi halaman berita">
          @if (! $posts->onFirstPage())
            <a href="{{ $posts->previousPageUrl() }}" rel="prev">← Sebelumnya</a>
          @endif
          <span>Halaman {{ $posts->currentPage() }} dari {{ $posts->lastPage() }}</span>
          @if ($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}" rel="next">Berikutnya →</a>
          @endif
        </nav>
      @endif
    </div>
  </section>
@endsection
