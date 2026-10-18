@php
    $school = \App\Support\SchoolSettings::contact();

    $footerContacts = collect([
        [
            'label' => 'Telepon',
            'value' => $school['phones']['general'] ?? '',
            'link' => filled($school['phones']['general'] ?? null) ? 'tel:' . preg_replace('/\D+/', '', $school['phones']['general']) : null,
        ],
        [
            'label' => 'WhatsApp',
            'value' => $school['whatsapp']['general'] ?? '',
            'link' => \App\Support\WhatsApp::link($school['whatsapp']['general'] ?? null),
        ],
        [
            'label' => 'Email',
            'value' => $school['emails']['general'] ?? '',
            'link' => filled($school['emails']['general'] ?? null) ? 'mailto:' . $school['emails']['general'] : null,
        ],
    ])->map(fn (array $item) => [
        'label' => $item['label'],
        'display' => filled($item['value']) ? $item['value'] : 'Nomor telepon, WhatsApp, dan email resmi belum dikonfigurasi.',
        // Tautan '#' tidak dirender sebagai <a> (lihat loop footer di bawah).
        'link' => $item['link'],
    ]);

    // Dropdown navigasi: label induk adalah tautan ke bagian TERATAS halaman
    // (bukan sekadar pemicu scroll), dan setiap isi submenu harus menunjuk ke
    // tujuan yang benar-benar ada. Submenu tanpa konten nyata sengaja belum
    // dipasang agar tidak menjadi tautan dekoratif.
    $navDropdowns = [
        'Akademik' => [
            'section' => 'akademik',
            'items' => [
                ['label' => 'Program Keahlian', 'url' => route('site.section', 'akademik') . '#program-keahlian'],
                ['label' => 'Guru & Tenaga Kependidikan', 'url' => route('site.section', 'profil') . '#guru'],
            ],
        ],
        'Kesiswaan' => [
            'section' => 'kesiswaan',
            'items' => [
                ['label' => 'Kegiatan Siswa', 'url' => route('site.section', 'kesiswaan') . '#kegiatan-siswa'],
                ['label' => 'Pencapaian Siswa', 'url' => route('site.section', 'kesiswaan') . '#pencapaian'],
            ],
        ],
        'Informasi' => [
            'section' => 'informasi',
            'items' => [
                ['label' => 'Berita', 'url' => route('berita.index')],
                ['label' => 'Agenda', 'url' => route('site.section', 'informasi') . '#agenda'],
            ],
        ],
    ];

    $navLinks = [
        ['label' => 'Prestasi', 'section' => 'prestasi'],
        ['label' => 'Industri', 'section' => 'industri'],
        ['label' => 'Magang Jepang', 'section' => 'jepang'],
        ['label' => 'Galeri', 'section' => 'galeri', 'url' => route('galeri.index')],
        ['label' => 'Alumni', 'section' => 'alumni'],
        ['label' => 'Kontak', 'section' => 'kontak'],
    ];

    $siteTitle = $title ?? 'SMK INFOKOM | Teknologi, Industri, Dunia';
    $siteDescription = $description ?? 'SMK INFOKOM menyiapkan siswa dengan keterampilan teknologi, pengalaman industri, dan kesempatan berkembang melalui pembelajaran berbasis proyek dan kerja sama industri.';
    $activeSection = $activeSection ?? null;
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#102a65">
  <meta name="robots" content="index,follow">
  <meta name="description" content="{{ $siteDescription }}">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="SMK INFOKOM">
  <meta property="og:title" content="{{ $siteTitle }}">
  <meta property="og:description" content="{{ $siteDescription }}">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $siteTitle }}">
  <meta name="twitter:description" content="{{ $siteDescription }}">
  <link rel="canonical" href="{{ url()->current() }}">
  <title>{{ $siteTitle }}</title>
  {{-- Font diunduh saat build Vite dan disajikan dari /build (self-hosted). --}}
  @vite(['resources/css/site.css', 'resources/js/site.js'])
</head>
<body>
  <header class="nav">
    <div class="container nav-in">
      <a class="brand" href="{{ route('home') }}" aria-label="SMK INFOKOM, beranda">
        <span class="detail-mark" aria-hidden="true">IF</span>
        <span><b>SMK INFOKOM</b></span>
      </a>
      <nav class="menu" id="menu" aria-label="Menu utama">
        <a href="{{ route('home') }}" @class(['active' => ($activeSection ?? 'home') === 'home']) @if (($activeSection ?? null) === 'home') aria-current="page" @endif>Beranda</a>
        {{-- Profil adalah satu halaman lengkap: tautan langsung ke bagian teratas,
             tanpa dropdown anchor yang membingungkan. --}}
        <a href="{{ route('site.section', 'profil') }}" @class(['active' => $activeSection === 'profil']) @if ($activeSection === 'profil') aria-current="page" @endif>Profil</a>
        @foreach ($navDropdowns as $label => $group)
          <div @class(['nav-dropdown', 'is-active' => $activeSection === $group['section']]) data-nav-dropdown>
            <span class="nav-parent-row">
              <a class="nav-parent" href="{{ route('site.section', $group['section']) }}" @if ($activeSection === $group['section']) aria-current="page" @endif>{{ $label }}</a>
              <button class="nav-caret" type="button" aria-expanded="false" aria-controls="nav-sub-{{ $group['section'] }}" aria-label="Tampilkan submenu {{ $label }}"></button>
            </span>
            <div class="nav-submenu" id="nav-sub-{{ $group['section'] }}" hidden>
              @foreach ($group['items'] as $item)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
              @endforeach
            </div>
          </div>
        @endforeach
        @foreach ($navLinks as $link)
          <a href="{{ $link['url'] ?? route('site.section', $link['section']) }}" @class(['active' => $activeSection === $link['section']])>{{ $link['label'] }}</a>
        @endforeach
      </nav>
      <div class="nav-actions">
        <a class="btn btn-accent" href="{{ route('site.section', 'ppdb') }}">Daftar PPDB</a>
        <button class="icon-btn burger" id="burger" type="button" aria-label="Buka menu" aria-expanded="false" data-ic="menu" data-s="22"></button>
      </div>
    </div>
  </header>

  <main>
    @yield('content')
  </main>

  <footer class="foot">
    <div class="container home-footer">
      <div>
        <a class="brand" href="{{ route('home') }}">
          <span class="detail-mark" aria-hidden="true">IF</span>
          <span><b>{{ $school['name'] ?? 'SMK INFOKOM' }}</b><small>Teknologi, industri, dunia</small></span>
        </a>
        <p>Sekolah vokasi yang menyiapkan lulusan untuk bekerja, berwirausaha, dan melanjutkan pendidikan.</p>
      </div>
      <div>
        <h3>Jelajahi</h3>
        <a href="{{ route('site.section', 'profil') }}">Profil sekolah</a>
        <a href="{{ route('site.section', 'akademik') }}">Program keahlian</a>
        <a href="{{ route('galeri.index') }}">Galeri kegiatan</a>
        <a href="{{ route('site.section', 'alumni') }}">Alumni</a>
      </div>
      <div>
        <h3>Kontak</h3>
        @foreach ($footerContacts as $item)
          @if (filled($item['link']))
            <a href="{{ $item['link'] }}">{{ $item['label'] }}: {{ $item['display'] }}</a>
          @else
            <span class="footer-contact-disabled">{{ $item['label'] }}: {{ $item['display'] }}</span>
          @endif
        @endforeach
        @php
          $fullAddress = collect([
              $school['address']['Alamat lengkap'] ?? '',
              $school['address']['RT/RW'] ?? '',
              $school['address']['Kelurahan/Desa'] ?? '',
              $school['address']['Kecamatan'] ?? '',
              $school['address']['Kota/Kabupaten'] ?? '',
              $school['address']['Provinsi'] ?? '',
              $school['address']['Kode pos'] ?? '',
          ])->filter()->implode(', ');
        @endphp
        @if (filled($fullAddress))
          <span class="footer-address">{{ $fullAddress }}</span>
        @else
          <span class="footer-address">Alamat sekolah belum dikonfigurasi. Silakan lengkapi data di panel admin.</span>
        @endif
      </div>
      <div>
        <h3>Informasi</h3>
        <a href="{{ route('site.section', 'ppdb') }}">PPDB</a>
        <a href="{{ route('site.section', 'informasi') }}">Berita & agenda</a>
        <a href="{{ route('site.section', 'kontak') }}">Hubungi sekolah</a>
        <a href="{{ route('site.section', 'jepang') }}">Magang Jepang</a>
      </div>
    </div>
    <div class="container foot-b">
      <span>{{ $school['name'] ?? 'SMK INFOKOM' }} · {{ now()->year }}</span>
      <span>Informasi publik sekolah</span>
    </div>
  </footer>
</body>
</html>
