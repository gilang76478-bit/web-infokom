@extends('layouts.site', [
    'activeSection' => 'profil',
    'title' => 'Guru dan Tenaga Kependidikan | SMK INFOKOM',
    'description' => 'Direktori resmi guru dan tenaga kependidikan SMK INFOKOM: pimpinan sekolah, tenaga pendidik di ruang kelas, dan tenaga kependidikan yang menopang layanan sekolah.',
])

@section('content')
  {{-- Remah navigasi Schema.org: Beranda → Profil sekolah → halaman ini. --}}
  @push('jsonld')
    {!! \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumb(
        'Guru dan Tenaga Kependidikan',
        route('guru.index'),
        [['name' => 'Profil sekolah', 'item' => route('site.section', 'profil')]],
    )) !!}
  @endpush

  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Tim sekolah</p>
      <h1>Guru dan Tenaga Kependidikan</h1>
      <p>Pendamping harian siswa di ruang kelas, bengkel praktik, dan ruang teknis — dari pimpinan sekolah hingga tenaga yang menopang layanan pendidikan setiap hari.</p>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      {{-- Pimpinan sekolah: hanya jabatan kepala/wakil kepala sekolah dari data. --}}
      <div class="sh-row">
        <div class="sh">
          <span class="detail-eyebrow">Struktur sekolah</span>
          <h2>Pimpinan sekolah</h2>
          <p>Bertanggung jawab atas arah, mutu, dan pelayanan pendidikan di SMK INFOKOM.</p>
        </div>
      </div>

      @if ($leaders->isNotEmpty())
        <div class="org-tree">
          <div class="org-level">
            @foreach ($leaders as $teacher)
              @include('guru._card', ['teacher' => $teacher])
            @endforeach
          </div>
        </div>
      @else
        <p class="detail-empty">Data pimpinan sekolah belum dipublikasikan. Informasi ini akan tampil di sini setelah diterbitkan melalui panel admin.</p>
      @endif

      {{-- Direktori: pendidik dan kependidikan dipisah berdasarkan jabatan
           pada data; grup yang kosong tidak dirender agar tidak ada
           placeholder tanpa isi. --}}
      <div class="sh-row">
        <div class="sh">
          <span class="detail-eyebrow">Direktori</span>
          <h2>Tenaga pendidik dan kependidikan</h2>
          <p>Nama, jabatan, dan bidang tugas sesuai data yang dikelola sekolah melalui panel admin. Tenaga pendidik mengajar di ruang kelas; tenaga kependidikan menopang administrasi dan fasilitas.</p>
        </div>
      </div>

      @if ($educators->isNotEmpty() || $support->isNotEmpty())
        @if ($educators->isNotEmpty())
          <h3 class="guru-group-title">Tenaga pendidik</h3>
          <div class="org-tree">
            <div class="org-level">
              @foreach ($educators as $teacher)
                @include('guru._card', ['teacher' => $teacher])
              @endforeach
            </div>
          </div>
        @endif

        @if ($support->isNotEmpty())
          <h3 class="guru-group-title">Tenaga kependidikan</h3>
          <div class="org-tree">
            <div class="org-level">
              @foreach ($support as $teacher)
                @include('guru._card', ['teacher' => $teacher])
              @endforeach
            </div>
          </div>
        @endif
      @else
        <p class="detail-empty">Belum ada data tenaga pendidik dan kependidikan lain yang dipublikasikan. Nama dan bidang tugas akan tampil di sini setelah dikelola serta diterbitkan melalui panel admin sekolah.</p>
      @endif

      <div class="program-actions">
        <a class="btn btn-brand" href="{{ route('site.section', 'akademik') }}">Lihat program keahlian</a>
        <a class="btn btn-line" href="{{ route('site.section', 'kontak') }}">Hubungi sekolah</a>
      </div>
    </div>
  </section>
@endsection
