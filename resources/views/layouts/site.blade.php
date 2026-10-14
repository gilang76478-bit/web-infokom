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
            'link' => filled($school['whatsapp']['general'] ?? null) ? 'https://wa.me/' . preg_replace('/\D+/', '', $school['whatsapp']['general']) : null,
        ],
        [
            'label' => 'Email',
            'value' => $school['emails']['general'] ?? '',
            'link' => filled($school['emails']['general'] ?? null) ? 'mailto:' . $school['emails']['general'] : null,
        ],
    ])->map(fn (array $item) => [
        'label' => $item['label'],
        'display' => filled($item['value']) ? $item['value'] : 'Nomor telepon, WhatsApp, dan email resmi belum dikonfigurasi.',
        'link' => $item['link'] ?? '#',
    ]);

    $navGroups = [
        'Profil' => [
            'section' => 'profil',
            'items' => [
                ['label' => 'Tentang Sekolah', 'anchor' => 'tentang'],
                ['label' => 'Sejarah', 'anchor' => 'sejarah'],
                ['label' => 'Visi & Misi', 'anchor' => 'visi-misi'],
                ['label' => 'Struktur Organisasi', 'anchor' => 'struktur-organisasi'],
                ['label' => 'Fasilitas', 'anchor' => 'fasilitas'],
            ],
        ],
        'Akademik' => [
            'section' => 'akademik',
            'items' => array_map(fn (string $label) => ['label' => $label], [
                'Program Keahlian', 'Kurikulum', 'Guru & Tenaga Kependidikan', 'Kalender Akademik',
            ]),
        ],
        'Kesiswaan' => [
            'section' => 'kesiswaan',
            'items' => array_map(fn (string $label) => ['label' => $label], [
                'OSIS', 'Ekstrakurikuler', 'Kegiatan Siswa', 'Tata Tertib', 'Beasiswa',
            ]),
        ],
        'Informasi' => [
            'section' => 'informasi',
            'items' => array_map(fn (string $label) => ['label' => $label], [
                'Berita', 'Pengumuman', 'Agenda',
            ]),
        ],
    ];

    $navLinks = collect([
        ['label' => 'Prestasi', 'section' => 'prestasi'],
        ['label' => 'Industri', 'section' => 'industri'],
        ['label' => 'Magang Jepang', 'section' => 'jepang'],
        ['label' => 'Galeri', 'section' => 'galeri'],
        ['label' => 'Alumni', 'section' => 'alumni'],
        ['label' => 'Kontak', 'section' => 'kontak'],
    ]);

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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        @foreach ($navGroups as $label => $group)
          <details @class(['nav-dropdown', 'is-active' => $activeSection === $group['section']])>
            <summary>{{ $label }}</summary>
            <div class="nav-submenu">
              @foreach ($group['items'] as $item)
                <a href="{{ route('site.section', $group['section']) }}{{ isset($item['anchor']) ? '#' . $item['anchor'] : '' }}">{{ $item['label'] }}</a>
              @endforeach
            </div>
          </details>
        @endforeach
        @foreach ($navLinks as $link)
          <a href="{{ route('site.section', $link['section']) }}" @class(['active' => $activeSection === $link['section']])>{{ $link['label'] }}</a>
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
        <a href="{{ route('site.section', 'galeri') }}">Galeri kegiatan</a>
        <a href="{{ route('site.section', 'alumni') }}">Alumni</a>
      </div>
      <div>
        <h3>Kontak</h3>
        @foreach ($footerContacts as $item)
          <a href="{{ $item['link'] }}">{{ $item['label'] }}: {{ $item['display'] }}</a>
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
