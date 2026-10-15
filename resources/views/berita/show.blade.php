@extends('layouts.site', [
    'activeSection' => 'informasi',
    'title' => $post->title . ' | Berita SMK INFOKOM',
    'description' => \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 160),
])

@section('content')
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
@endsection
