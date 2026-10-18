@extends('layouts.site', ['activeSection' => 'home'])

@section('content')
  <section class="home-hero">
    <div class="container home-hero-in">
      <div class="home-copy">
        <p class="detail-eyebrow">Sekolah vokasi · Bogor</p>
        <h1>Belajar hari ini. Berkarya untuk masa depan.</h1>
        <p>Bangun keterampilan melalui praktik, proyek, dan pengalaman industri bersama SMK INFOKOM.</p>
        <div class="cta-row">
          <a class="btn btn-accent" href="{{ route('site.section', 'ppdb') }}">Jelajahi PPDB</a>
          <a class="btn btn-ghost" href="{{ route('site.section', 'profil') }}">Kenali sekolah</a>
        </div>
        <div class="hero-badges" aria-label="Keunggulan utama">
          <span>Project Based Learning</span>
          <span>Praktik Industri</span>
          <span>Magang Jepang</span>
        </div>
      </div>
      <div class="home-mosaic" aria-label="Keunggulan SMK INFOKOM">
        <div class="home-tile home-tile-school"><span class="tile-mark">IF</span><b>Belajar lewat praktik</b><small>Proyek nyata, keterampilan relevan</small></div>
        <div class="home-tile home-tile-japan"><span class="japan-sun"></span><b>Magang Jepang</b><small>Siapkan langkah ke dunia internasional</small></div>
        <div class="home-tile home-tile-program"><b>{{ count($site['programs']) }}</b><span>program keahlian</span></div>
        <div class="home-tile home-tile-industry"><b>{{ count($site['partners']) }}</b><span>mitra industri</span></div>
      </div>
    </div>
    <div class="home-ribbon"><div class="container"><strong>Pengumuman</strong><span>{{ $site['announce']->first() ?? 'Informasi terbaru SMK INFOKOM' }}</span><a href="{{ route('site.section', 'informasi') }}">Semua informasi <span aria-hidden="true">→</span></a></div></div>
  </section>

  <section class="sec home-highlights">
    <div class="container stats-grid">
      <article class="stat-card">
        <span class="stat-label">Program Keahlian</span>
        <strong>{{ count($site['programs']) }}+</strong>
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
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Pusat informasi</p><h2>Sebelas bagian, satu beranda</h2><p>Seluruh informasi SMK INFOKOM tersusun dalam sebelas bagian — pilih bagian yang ingin Anda kenali lebih dekat.</p></div></div>
      <div class="home-explore-grid">
        @foreach ($explore as $index => $item)
          <a class="home-explore-card" href="{{ $item['href'] }}">
            <span class="explore-index" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
          <span class="why-icon">01</span>
          <h3>Project Based Learning</h3>
          <p>Siswa belajar melalui proyek nyata agar keterampilan lebih aplikatif dan siap dipakai di dunia kerja.</p>
        </article>
        <article class="why-card">
          <span class="why-icon">02</span>
          <h3>Praktik Industri</h3>
          <p>Kolaborasi dengan mitra industri membuat siswa terbiasa dengan standar kerja profesional sejak dini.</p>
        </article>
        <article class="why-card">
          <span class="why-icon">03</span>
          <h3>Guru Profesional</h3>
          <p>Pembimbing yang fokus pada pengembangan soft skill, hard skill, dan karakter siswa.</p>
        </article>
        <article class="why-card">
          <span class="why-icon">04</span>
          <h3>Persiapan Internasional</h3>
          <p>Program seperti Magang Jepang menjadi langkah nyata menuju pengalaman kerja di level global.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="home-feature-band">
    <div class="container feature-band-grid">
      <div class="feature-item">
        <strong>01</strong>
        <span>Belajar berbasis proyek</span>
      </div>
      <div class="feature-item">
        <strong>02</strong>
        <span>Praktik industri terukur</span>
      </div>
      <div class="feature-item">
        <strong>03</strong>
        <span>Guru dan pembimbing profesional</span>
      </div>
      <div class="feature-item">
        <strong>04</strong>
        <span>Persiapan kerja dan internasional</span>
      </div>
    </div>
  </section>

  <section class="sec home-section" id="profil">
    <div class="container home-about">
      <div class="home-section-label"><span>Profil</span><div class="home-portrait" aria-hidden="true"><i></i><b>INFOKOM</b><small>Belajar · Berkarya · Bertumbuh</small></div></div>
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
          <a href="{{ route('berita.show', $post['slug']) }}"><span>{{ $post['cat'] }} · {{ $post['d'] }}</span><h3>{{ $post['t'] }}</h3><p>{{ $post['x'] }}</p></a>
        @empty
          <p class="detail-empty">Belum ada kabar terbaru. Artikel akan tampil di sini setelah dipublikasikan.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="sec home-section">
    <div class="container home-about home-industry">
      <div><p class="detail-eyebrow">Industri</p><h2>Belajar terhubung dengan dunia kerja</h2><p>Kolaborasi membuka pengalaman praktik dan wawasan profesi bagi siswa.</p><a class="link" href="{{ route('site.section', 'industri') }}">Kenali mitra industri <span aria-hidden="true">→</span></a></div>
      <div class="home-partners">@foreach ($site['partners']->take(6) as $partner)<span>{{ $partner['n'] }}</span>@endforeach</div>
    </div>
  </section>

  <section class="sec mist home-section">
    <div class="container">
      <div class="sh-row"><div class="sh"><p class="detail-eyebrow">Galeri</p><h2>Belajar, berkarya, bersama</h2></div><a class="link" href="{{ route('galeri.index') }}">Lihat kegiatan lain <span aria-hidden="true">→</span></a></div>
      <div class="home-gallery">
        @foreach ($site['gal']->take(4) as $item)
          <a href="{{ route('galeri.index') }}"><b>{{ $item['t'] }}</b><small>{{ $item['c'] }}</small></a>
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
