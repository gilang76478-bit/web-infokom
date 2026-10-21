<aside class="admin-sidebar" id="adminSidebar" aria-label="Navigasi admin">
  <a class="admin-brand" href="{{ route('filament.admin.pages.dashboard') }}">
    <img class="brand-logo" src="{{ \App\Support\SchoolSettings::logoUrl() }}" width="38" height="38" alt="">
    <span><b>SMK INFOKOM</b><small>ADMIN PANEL</small></span>
  </a>
  <nav class="admin-nav">
    <p class="admin-nav-label">Ruang kerja</p>
    <a href="{{ route('filament.admin.pages.dashboard') }}"><span class="admin-nav-icon" aria-hidden="true">▦</span>Dashboard</a>
    <a href="{{ route('admin.settings.index') }}" @class(['is-active' => request()->routeIs('admin.settings.*')]) @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">⚙</span>Pengaturan sekolah</a>
    <a href="{{ route('filament.admin.pages.pengaturan-profil') }}" @class(['is-active' => request()->routeIs('filament.admin.pages.pengaturan-profil')]) @if (request()->routeIs('filament.admin.pages.pengaturan-profil')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">✎</span>Pengaturan Profil</a>
    <p class="admin-nav-label">Konten website</p>
    <a href="{{ route('filament.admin.resources.posts.index') }}" @class(['is-active' => request()->routeIs('filament.admin.resources.posts.*')]) @if (request()->routeIs('filament.admin.resources.posts.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">▤</span>Berita</a>
    <a href="{{ route('filament.admin.resources.categories.index') }}" @class(['is-active' => request()->routeIs('filament.admin.resources.categories.*')]) @if (request()->routeIs('filament.admin.resources.categories.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">⌗</span>Kategori berita</a>
    <a href="{{ route('filament.admin.resources.gallery-items.index') }}" @class(['is-active' => request()->routeIs('filament.admin.resources.gallery-items.*')]) @if (request()->routeIs('filament.admin.resources.gallery-items.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">▧</span>Galeri foto</a>
    @foreach ([['akademik', 'Program keahlian', '◇'], ['guru', 'Guru & TTK', '☺'], ['agenda', 'Agenda', '▦'], ['prestasi', 'Prestasi', '✦'], ['industri', 'Mitra industri', '⌘'], ['fasilitas', 'Fasilitas sekolah', '⌂'], ['lowongan', 'Lowongan BKK', '✉'], ['jepang', 'Magang Jepang', '◉'], ['reel-jepang', 'Reel Magang Jepang', '▶'], ['alumni', 'Alumni', '◎'], ['tracer', 'Tracer study', '⌁'], ['pengumuman', 'Pengumuman', '◌']] as [$slug, $label, $icon])
      <a href="{{ route('admin.content.index', $slug) }}" @class(['is-active' => request()->routeIs('admin.content.*') && request()->route('type') === $slug]) @if (request()->route('type') === $slug) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
    @endforeach
    <a href="{{ route('admin.ppdb.index') }}" @class(['is-active' => request()->routeIs('admin.ppdb.*')]) @if (request()->routeIs('admin.ppdb.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">▣</span>Pendaftar PPDB</a>
    <a href="{{ route('admin.messages.index') }}" @class(['is-active' => request()->routeIs('admin.messages.*')]) @if (request()->routeIs('admin.messages.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">✉</span>Pesan masuk @php($unreadMessages = \App\Models\ContactMessage::where('is_read', false)->count()) @if ($unreadMessages)<span class="admin-nav-count">{{ $unreadMessages }}</span>@endif</a>
  </nav>
  <div class="admin-sidebar-bottom">
    <a href="{{ route('home') }}" target="_blank" rel="noopener"><span class="admin-nav-icon" aria-hidden="true">↗</span>Lihat website</a>
    <form method="post" action="{{ route('filament.admin.auth.logout') }}">@csrf<button type="submit"><span class="admin-nav-icon" aria-hidden="true">⇥</span>Keluar</button></form>
  </div>
</aside>
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" hidden></div>
