@extends('layouts.site', [
    'activeSection' => 'informasi',
    'title' => $post->title . ' | Berita SMK INFOKOM',
    'description' => \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 160),
])

{{-- Open Graph: tautan artikel dibagikan sebagai tipe "article" lengkap
     dengan waktu terbit, waktu ubah, dan kategori. Gambar share memakai
     foto artikel bila ada, dan kembali ke logo sekolah untuk artikel
     yang belum mempunyai foto. --}}
@section('ogType', 'article')
@section('ogImage', $post->image_url ?: \App\Support\SchoolSettings::logoUrl('images/logo-512.png'))

@push('og')
  <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
  @if ($post->updated_at)
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
  @endif
  @if ($post->category)
    <meta property="article:section" content="{{ $post->category->name }}">
  @endif
@endpush

@section('content')
  {{-- Remah navigasi Schema.org: Beranda → Berita & Artikel → artikel ini.
       Node Article menyertai remah agar halaman ini layak tampil sebagai
       rich result berita. --}}
  @push('jsonld')
    {!! \App\Support\StructuredData::script(
        \App\Support\StructuredData::breadcrumb(
            $post->title,
            route('berita.show', $post),
            [['name' => 'Berita & Artikel', 'item' => route('berita.index')]],
        ),
        \App\Support\StructuredData::article(
            $post->title,
            route('berita.show', $post),
            \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 160),
            $post->published_at->toIso8601String(),
            $post->updated_at?->toIso8601String(),
            $post->category?->name,
            $post->image_url,
        ),
    ) !!}
  @endpush

  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">{{ $post->category?->name ?? 'Berita' }}</p>
      <h1>{{ $post->title }}</h1>
      <p>{{ $post->excerpt }}</p>
      <p class="berita-date">Dipublikasikan {{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</p>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <div class="berita-body">
        @if (filled($post->image_url))
          <figure class="berita-figure">
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
          </figure>
        @endif
        @php
          $paragraphs = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($post->body ?: $post->excerpt)),
            fn (string $line) => trim($line) !== '',
          ));
        @endphp
        @foreach ($paragraphs as $paragraph)
          <p>{{ $paragraph }}</p>
        @endforeach

        <div class="program-actions">
          <a class="btn btn-accent" href="{{ route('berita.index') }}">Semua berita</a>
          @if ($post->category)
            <a class="btn btn-brand" href="{{ route('berita.index', ['kategori' => $post->category->slug]) }}">Berita {{ $post->category->name }}</a>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- Berita terkait: kategori sama lebih dulu, lalu terbaru (maksimal tiga). --}}
  @if ($related->isNotEmpty())
    <section class="sec detail-content berita-related">
      <div class="container">
        <div class="sh-row">
          <div class="sh">
            <span class="detail-eyebrow">Berita terkait</span>
            <h2>Artikel lain untuk Anda</h2>
          </div>
        </div>
        <div class="detail-list">
          @foreach ($related as $item)
            <article class="detail-row">
              <span class="detail-tag">{{ $item->category?->name ?? 'Berita' }}</span>
              <div>
                <h3><a href="{{ route('berita.show', $item) }}">{{ $item->title }}</a></h3>
                <p>{{ $item->excerpt }}</p>
                <small>{{ $item->published_at->locale('id')->translatedFormat('j F Y') }}</small>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@endsection
