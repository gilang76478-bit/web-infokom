@extends('layouts.admin', ['title' => 'Dashboard Admin'])

@section('content')
  <div class="admin-page-heading"><div><p class="admin-eyebrow">Ruang kerja administrator</p><h1>Dashboard</h1><p>Ringkasan konten dan akses cepat ke pengelolaan website.</p></div><span class="admin-date">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span></div>
  <section class="admin-overview" aria-label="Ringkasan data">
    @foreach ($counts as [$label, $count, $slug])
      @if ($slug)
        <a class="admin-metric" data-search-item href="{{ $slug === 'ppdb' ? route('admin.ppdb.index') : ($slug === 'messages' ? route('admin.messages.index') : route('admin.content.index', $slug)) }}"><span>{{ $label }}</span><b>{{ number_format($count, 0, ',', '.') }}</b><small>Buka pengelolaan <span aria-hidden="true">↗</span></small></a>
      @else
        <div class="admin-metric" data-search-item><span>{{ $label }}</span><b>{{ number_format($count, 0, ',', '.') }}</b><small>Akun terdaftar</small></div>
      @endif
    @endforeach
  </section>
  @php
    $fastLinks = [
      ['label' => 'Pengaturan sekolah', 'desc' => 'Nama, kontak, alamat, maps', 'url' => route('admin.settings.index'), 'type' => 'primary'],
      ['label' => 'PPDB', 'desc' => 'Pendaftar & jadwal', 'url' => route('admin.ppdb.index'), 'type' => 'secondary'],
      ['label' => 'Pesan masuk', 'desc' => 'Inbox kontak & pertanyaan', 'url' => route('admin.messages.index'), 'type' => 'secondary'],
      ['label' => 'Program keahlian', 'desc' => 'Edit jurusan & kompetensi', 'url' => route('admin.content.index', 'akademik'), 'type' => 'secondary'],
    ];
  @endphp
  <section class="admin-fast-grid" aria-label="Akses cepat pengelolaan">
    @foreach ($fastLinks as $link)
      <a class="admin-fast-card {{ $link['type'] === 'primary' ? 'is-primary' : '' }}" href="{{ $link['url'] }}">
        <span class="admin-fast-label">Akses cepat</span>
        <strong>{{ $link['label'] }}</strong>
        <small>{{ $link['desc'] }}</small>
      </a>
    @endforeach
  </section>
  <section class="admin-quick-panel"><div><p class="admin-eyebrow">Kelola website</p><h2>Pilih bagian yang ingin diperbarui</h2><p>Perubahan konten tersimpan ke database dan dapat ditinjau pada halaman publik.</p></div><a class="admin-primary-link" href="{{ route('home') }}" target="_blank" rel="noopener">Pratinjau website <span aria-hidden="true">↗</span></a></section>
@endsection
