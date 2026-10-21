@extends('layouts.site', [
    'activeSection' => 'akademik',
    'title' => 'Bursa Kerja Khusus (BKK) | SMK INFOKOM',
    'description' => 'Bursa Kerja Khusus (BKK) SMK INFOKOM Kota Bogor: informasi lowongan kerja, rekrutmen, dan kabar penempatan alumni melalui jejaring dunia usaha dan industri.',
])

@section('content')
  {{-- Remah navigasi Schema.org: Beranda → Program keahlian → halaman ini. --}}
  @push('jsonld')
    {!! \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumb(
        'Bursa Kerja Khusus (BKK)',
        route('bkk.index'),
        [['name' => 'Program keahlian', 'item' => route('site.section', 'akademik')]],
    )) !!}
  @endpush

  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Karier dan dunia industri</p>
      <h1>Bursa Kerja Khusus (BKK)</h1>
      <p>Jembatan sekolah dengan dunia kerja: informasi lowongan, rekrutmen, dan peluang karier bagi siswa serta lulusan SMK INFOKOM Kota Bogor.</p>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <div class="bkk-about">
        <div class="bkk-about-logo">
          <img src="{{ asset('images/bkk-logo.jpg') }}" alt="Logo Bursa Kerja Khusus (BKK) SMK INFOKOM Kota Bogor" width="260" height="260">
        </div>
        <div class="bkk-about-body">
          <span class="detail-eyebrow">Tentang BKK</span>
          <h2>Mempertemukan lulusan dengan peluang kerja</h2>
          <p>Bursa Kerja Khusus (BKK) SMK INFOKOM Kota Bogor menyampaikan informasi lowongan dan rekrutmen dari dunia usaha serta industri kepada siswa dan alumni. Setiap informasi yang masuk diverifikasi sekolah melalui jejaring mitra DU/DI sebelum ditayangkan.</p>
          <ul class="detail-steps">
            <li><span>01</span><div><b>Informasi lowongan</b><p>Poster dan pengumuman lowongan kerja dari mitra industri ditayangkan secara berkala pada halaman ini.</p></div></li>
            <li><span>02</span><div><b>Penempatan alumni</b><p>Kabar penempatan lulusan di perusahaan mitra menjadi gambaran nyata jalur karier setelah lulus.</p></div></li>
            <li><span>03</span><div><b>Penjelasan karier</b><p>Siswa dan orang tua dapat berkonsultasi mengenai peluang kerja, PKL, dan rekrutmen melalui kanal kontak sekolah.</p></div></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="sec mist bkk-lowongan">
    <div class="container">
      <div class="sh-row">
        <div class="sh">
          <span class="detail-eyebrow">Info lowongan</span>
          <h2>Poster lowongan terbaru</h2>
          <p>Lowongan yang ditayangkan berasal dari mitra dunia usaha dan industri yang bekerja sama dengan sekolah.</p>
        </div>
      </div>
      @if ($vacancies->isNotEmpty())
        <div class="bkk-posters">
          @foreach ($vacancies as $vacancy)
            <a class="bkk-poster"
               href="{{ filled($vacancy->link) ? $vacancy->link : ($vacancy->image_url ?: route('site.section', 'kontak')) }}"
               @if (filled($vacancy->link)) target="_blank" rel="noopener noreferrer" @endif
               aria-label="Poster lowongan: {{ $vacancy->title }}">
              @if (filled($vacancy->image_url))
                <img src="{{ $vacancy->image_url }}" alt="Poster lowongan {{ $vacancy->title }}" loading="lazy">
              @else
                <div class="bkk-poster-placeholder">Poster lowongan</div>
              @endif
              <div class="bkk-poster-body">
                <b>{{ $vacancy->title }}</b>
                <small>{{ $vacancy->posted_at->locale('id')->translatedFormat('j F Y') }}</small>
                @if (filled($vacancy->description))<p>{{ $vacancy->description }}</p>@endif
              </div>
            </a>
          @endforeach
        </div>
      @else
        <p class="detail-empty">Belum ada poster lowongan yang ditayangkan. Poster baru akan tampil di sini setelah ditambahkan melalui panel admin.</p>
      @endif
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <div class="sh-row">
        <div class="sh">
          <span class="detail-eyebrow">Kabar BKK</span>
          <h2>Berita penempatan dan rekrutmen</h2>
          <p>Kabar terbaru dari Bursa Kerja Khusus, termasuk penempatan alumni pada perusahaan mitra.</p>
        </div>
      </div>
      @if ($posts->isNotEmpty())
        <div class="home-news">
          @foreach ($posts as $post)
            <a href="{{ route('berita.show', $post->slug) }}">
              <span>{{ $post->category?->name ?? 'BKK' }} · {{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</span>
              <h3>{{ $post->title }}</h3>
              <p>{{ $post->excerpt }}</p>
            </a>
          @endforeach
        </div>
      @else
        <p class="detail-empty">Belum ada berita BKK yang dipublikasikan. Artikel penempatan dan rekrutmen akan tampil di sini.</p>
      @endif

      <div class="program-actions">
        @php($industryWa = \App\Support\WhatsApp::link(
            $contact['whatsapp']['industry'] ?? $contact['whatsapp']['general'] ?? null,
            'Halo SMK INFOKOM, saya ingin menanyakan informasi Bursa Kerja Khusus (BKK) dan lowongan kerja terbaru.',
        ))
        @if (filled($industryWa))
          <a class="btn btn-brand" href="{{ $industryWa }}" target="_blank" rel="noopener noreferrer">Tanya lowongan via WhatsApp</a>
        @endif
        <a class="btn btn-line" href="{{ route('site.section', 'industri') }}">Lihat mitra industri</a>
        <a class="btn btn-line" href="{{ route('berita.index') }}">Semua berita sekolah</a>
      </div>
    </div>
  </section>
@endsection
