<header class="admin-topbar">
  <button class="admin-icon-button admin-menu-toggle" id="adminMenuToggle" type="button" aria-label="Buka navigasi admin" aria-expanded="false">☰</button>
  <label class="admin-global-search"><span aria-hidden="true">⌕</span><input id="adminSearch" type="search" placeholder="Cari data pada halaman ini…" autocomplete="off"><kbd>/</kbd></label>
  <a class="admin-public-link" href="{{ route('home') }}" target="_blank" rel="noopener">Buka website publik <span aria-hidden="true">↗</span></a>
  <div class="admin-user"><span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span><b>{{ auth()->user()->name }}</b><small>Administrator</small></span></div>
</header>
