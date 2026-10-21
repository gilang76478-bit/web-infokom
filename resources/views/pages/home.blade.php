@extends('layouts.site', ['activeSection' => 'home'])

@section('content')
  <section class="home-hero">
    <div class="container home-hero-in">
      <div class="home-copy">
        <p class="detail-eyebrow">Sekolah menengah kejuruan · Bogor</p>
        <h1>SMK Informatika dan Telekomunikasi Bogor</h1>
        <p>Siswa belajar lewat praktik, proyek, dan pengalaman di industri. Tersedia {{ count($site['programs']) }} program keahlian dengan {{ count($site['partners']) }} mitra industri.</p>
        <div class="cta-row">
          <a class="btn btn-brand" href="{{ route('site.section', 'ppdb') }}">Informasi PPDB</a>
          <a class="btn btn-line" href="{{ route('site.section', 'profil') }}">Profil sekolah</a>
        </div>
      </div>
      <div class="home-emblem">
        <img src="{{ \App\Support\SchoolSettings::logoUrl('images/logo-512.png') }}" width="300" height="300" alt="Logo SMK Informatika dan Telekomunikasi Bogor">
        <dl>
          <div><dt>Program keahlian</dt><dd>{{ count($site['programs']) }}</dd></div>
          <div><dt>Mitra industri</dt><dd>{{ count($site['partners']) }}</dd></div>
        </dl>
      </div>
    </div>
    <div class="home-ribbon"><div class="container"><strong>Pengumuman</strong><span>{{ $site['announce']->first() ?? 'Informasi terbaru SMK INFOKOM' }}</span><a href="{{ route('site.section', 'informasi') }}">Semua informasi <span aria-hidden="true">→</span></a></div></div>
  </section>

  <section class="sec home-highlights">
    <div class="container stats-grid">
      <article class="stat-card">
        <span class="stat-label">Program Keahlian</span>
        <strong>{{ count($site['programs']) }}</strong>
        <small>Jurusan unggulan yang siap membentuk kompetensi</small>
      </article>
      <article class="stat-card">
        <span class="stat-label">Mitra Industri</span>
        <strong>{{ count($site['partners']) }}</strong>
        <small>Kerja sama nyata untuk pengalaman kerja dan praktik</small>
      </article>
      <article class="stat-card">
        <span class="stat-label">Alumni Terdata</span>
        <strong>{{ count($site['alum']) }}</strong>
        <small>Profil alumni yang tercatat di website sekolah</small>
      </article>
      <article class="stat-card">
        <span class="stat-label">Tahap Magang Jepang</span>
        <strong>{{ count($site['jsteps']) }}</strong>
        <small>Tahap persiapan yang dapat dipelajari calon peserta</small>
      </article>
    </div>
  </section>

  <section class="sec home-section home-explore" id="jelajahi">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Jelajahi</p><h2>Informasi sekolah</h2><p>Pilih bagian yang ingin Anda ketahui lebih lanjut.</p></div></div>
      <div class="home-explore-grid">
        @foreach ($explore as $item)
          <a class="home-explore-card" href="{{ $item['href'] }}">
            <p class="explore-eyebrow">{{ $item['eyebrow'] }}</p>
            <h3>{{ $item['title'] }}</h3>
            <p>{{ $item['intro'] }}</p>
            @if (filled($item['count_label']))<small>{{ $item['count_label'] }}</small>@endif
            <b aria-hidden="true">→</b>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="sec home-section home-why">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Kenapa kami</p><h2>Lingkungan belajar yang mendorong siswa berkembang</h2></div></div>
      <div class="why-grid">
        <article class="why-card">
          <h3>Project Based Learning</h3>
          <p>Siswa belajar melalui proyek nyata agar keterampilan lebih aplikatif dan siap dipakai di dunia kerja.</p>
        </article>
        <article class="why-card">
          <h3>Praktik Industri</h3>
          <p>Kolaborasi dengan mitra industri membuat siswa terbiasa dengan standar kerja profesional sejak dini.</p>
        </article>
        <article class="why-card">
          <h3>Guru Profesional</h3>
          <p>Pembimbing yang fokus pada pengembangan soft skill, hard skill, dan karakter siswa.</p>
        </article>
        <article class="why-card">
          <h3>Persiapan Internasional</h3>
          <p>Program seperti Magang Jepang menjadi langkah nyata menuju pengalaman kerja di level global.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="sec home-section" id="profil">
    <div class="container home-about">
      <div class="home-section-label"><span>Profil</span></div>
      {{-- Foto bagian profil (gedung sekolah / kepala sekolah): diunggah
           melalui panel Pengaturan Profil, tanpa foto kolom gambar dilepas. --}}
      @if (filled($site['home_photo'] ?? null))
        <div class="home-about-media">
          <img src="{{ $site['home_photo'] }}" alt="Gedung sekolah SMK INFOKOM" loading="lazy" decoding="async">
        </div>
      @endif
      <div><h2>Belajar dengan mengerjakan hal yang nyata</h2><p>Lingkungan belajar vokasi yang membantu siswa menemukan potensi, menguasai keterampilan, dan melangkah dengan percaya diri.</p><a class="link" href="{{ route('site.section', 'profil') }}">Kenali visi, misi, dan profil sekolah <span aria-hidden="true">→</span></a></div>
    </div>
  </section>

  <section class="sec mist home-section">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Akademik</p><h2>Pilih jalur, bangun keahlian</h2><p>Program vokasi untuk teknologi, desain, bisnis, dan layanan.</p></div><a class="link" href="{{ route('site.section', 'akademik') }}">Lihat semua program <span aria-hidden="true">→</span></a></div>
      <div class="home-programs">
        @foreach ($site['programs']->take(3) as $program)
          <a class="home-program" href="{{ route('program.show', $program['slug']) }}"><span>{{ $program['short'] }}</span><h3>{{ $program['name'] }}</h3><p>{{ $program['desc'] }}</p><b>Detail program <span aria-hidden="true">→</span></b></a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="sec home-section">
    <div class="container home-two">
      <div><div class="sh"><p class="detail-eyebrow">Kesiswaan &amp; prestasi</p><h2>Ruang untuk mencoba dan berprestasi</h2><p>Pengalaman belajar tumbuh lewat karya, kompetisi, dan kegiatan bersama.</p></div>
        @forelse ($site['prest']->take(2) as $achievement)
          <article class="home-achievement"><span>{{ $achievement['y'] }}</span><div><h3>{{ $achievement['t'] }}</h3><p>{{ $achievement['who'] }} · {{ $achievement['lv'] }}</p></div></article>
        @empty
          <p class="detail-empty">Pencapaian siswa akan diumumkan di halaman ini setelah dikonfirmasi sekolah.</p>
        @endforelse
        <a class="link" href="{{ route('site.section', 'prestasi') }}">Jelajahi kegiatan dan prestasi <span aria-hidden="true">→</span></a>
      </div>
      <aside class="home-japan"><p class="detail-eyebrow">Magang Jepang</p><h2>Langkah terarah menuju pengalaman di Jepang</h2><p>Kenali tahapan program, persiapan bahasa, dan seleksi peserta.</p><a class="btn btn-accent" href="{{ route('site.section', 'jepang') }}">Lihat tahapan program</a></aside>
    </div>
  </section>

  <section class="sec mist home-section">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Informasi</p><h2>Kabar dan agenda sekolah</h2></div><a class="link" href="{{ route('berita.index') }}">Semua kabar <span aria-hidden="true">→</span></a></div>
      <div class="home-news">
        @forelse ($site['news']->take(3) as $post)
          <a href="{{ route('berita.show', $post['slug']) }}">
            @if (filled($post['i'] ?? null))<img class="home-news-photo" src="{{ $post['i'] }}" alt="{{ $post['t'] }}" loading="lazy" decoding="async">@endif
            <span>{{ $post['cat'] }} · {{ $post['d'] }}</span><h3>{{ $post['t'] }}</h3><p>{{ $post['x'] }}</p>
          </a>
        @empty
          <p class="detail-empty">Belum ada kabar terbaru. Artikel akan tampil di sini setelah dipublikasikan.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="sec home-section">
    <div class="container home-about home-industry">
      <div><p class="detail-eyebrow">Industri</p><h2>Belajar terhubung dengan dunia kerja</h2><p>Kolaborasi membuka pengalaman praktik dan wawasan profesi bagi siswa.</p><a class="link" href="{{ route('site.section', 'industri') }}">Kenali mitra industri <span aria-hidden="true">→</span></a></div>
      <div class="home-partners">
        @if ($site['partners']->isNotEmpty())
          <div class="mitra-marquee to-left home-partners-marquee">
            <div class="mitra-marquee-track">
              @foreach ([$site['partners'], $site['partners']] as $copyIndex => $items)
                @foreach ($items as $partner)
                  <div class="mitra-marquee-item" @if($copyIndex > 0) aria-hidden="true" @endif>
                    <span class="mitra-marquee-tile">
                      @if (filled($partner['logo']))
                        <img src="{{ $partner['logo'] }}" alt="{{ $partner['n'] }}" loading="lazy" width="132" height="132">
                      @endif
                    </span>
                    <span class="mitra-marquee-name">{{ $partner['n'] }}</span>
                  </div>
                @endforeach
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </section>

  <section class="sec mist home-section">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Galeri</p><h2>Belajar, berkarya, bersama</h2></div><a class="link" href="{{ route('galeri.index') }}">Lihat kegiatan lain <span aria-hidden="true">→</span></a></div>
      <div class="home-gallery">
        @foreach ($site['gal']->take(4) as $item)
          <a href="{{ route('galeri.index') }}" @if (filled($item['u'] ?? null)) class="home-gallery-photo" @endif>
            @if (filled($item['u'] ?? null))<img src="{{ $item['u'] }}" alt="{{ $item['t'] }}" loading="lazy" decoding="async">@endif
            <b>{{ $item['t'] }}</b><small>{{ $item['c'] }}</small>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="sec home-section">
    <div class="container home-alumni">
      <div><p class="detail-eyebrow">Alumni</p><h2>Bekal belajar yang ikut melangkah</h2><a class="link" href="{{ route('site.section', 'alumni') }}">Cerita alumni dan tracer study <span aria-hidden="true">→</span></a></div>
      @if ($site['alum']->isNotEmpty())
        <blockquote>@if (filled($site['alum']->first()['q']))<p>“{{ $site['alum']->first()['q'] }}”</p>@endif<footer><b>{{ $site['alum']->first()['n'] }}</b><span>{{ $site['alum']->first()['r'] }}</span></footer></blockquote>
      @endif
    </div>
  </section>

  <section class="home-ppdb">
    <div class="container home-ppdb-in"><div><p class="detail-eyebrow">PENERIMAAN MURID BARU</p><h2>Mulai langkah berikutnya bersama INFOKOM.</h2><p>Informasi jalur pendaftaran dan jadwal seleksi tersedia di halaman PPDB.</p></div><a class="btn btn-accent" href="{{ route('site.section', 'ppdb') }}">Informasi PPDB <span aria-hidden="true">→</span></a></div>
  </section>
@endsection
