<aside class="admin-sidebar" id="adminSidebar" aria-label="Navigasi admin">
  <a class="admin-brand" href="{{ route('filament.admin.pages.dashboard') }}">
    <span class="admin-brand-mark" aria-hidden="true">IF</span>
    <span><b>SMK INFOKOM</b><small>ADMIN PANEL</small></span>
  </a>
  <nav class="admin-nav">
    <p class="admin-nav-label">Ruang kerja</p>
    <a href="{{ route('filament.admin.pages.dashboard') }}"><span class="admin-nav-icon" aria-hidden="true">▦</span>Dashboard</a>
    <a href="{{ route('admin.settings.index') }}" @class(['is-active' => request()->routeIs('admin.settings.*')]) @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif><span class="admin-nav-icon" aria-hidden="true">⚙</span>Pengaturan sekolah</a>
    <p class="admin-nav-label">Konten website</p>
    @foreach ([['akademik', 'Program keahlian', '◇'], ['berita', 'Berita', '▤'], ['agenda', 'Agenda', '▦'], ['prestasi', 'Prestasi', '✦'], ['galeri', 'Galeri', '▧'], ['industri', 'Mitra industri', '⌘'], ['jepang', 'Magang Jepang', '◉'], ['alumni', 'Alumni', '◎'], ['tracer', 'Tracer study', '⌁'], ['pengumuman', 'Pengumuman', '◌']] as [$slug, $label, $icon])
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
