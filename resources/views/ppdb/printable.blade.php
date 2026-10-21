@extends('layouts.site', [
    'activeSection' => 'ppdb',
    'title' => 'Formulir Pendaftaran Calon Murid Baru | SMK INFOKOM',
    'description' => 'Cetak formulir pendaftaran calon murid baru SMK INFOKOM, isi dengan huruf cetak, lalu serahkan langsung ke sekolah bersama dokumen persyaratan.',
])

@section('content')
  {{-- Remah navigasi Schema.org: Beranda → PPDB → Formulir cetak. --}}
  @push('jsonld')
    {!! \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumb(
        'Formulir Pendaftaran Calon Murid Baru',
        route('ppdb.printable'),
        [['name' => 'Penerimaan murid baru', 'item' => route('site.section', 'ppdb')]],
    )) !!}
  @endpush

  @php
      // Alamat lengkap untuk kop surat, disusun dari pengaturan kontak;
      // hanya baris yang terisi yang digabung agar tidak muncul koma kosong.
      $letterAddress = collect($contact['address'] ?? [])->filter()->implode(', ');
      $requirements = $spmb['requirements'] ?? [];
  @endphp

  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Penerimaan murid baru</p>
      <h1>Formulir Pendaftaran Calon Murid Baru</h1>
      <p>Untuk pendaftaran langsung ke sekolah: cetak formulir ini atau unduh versi PDF-nya, isi dengan huruf cetak, lalu serahkan ke panitia PPDB bersama dokumen persyaratan. Pendaftaran daring melalui website tetap tersedia.</p>
      <div class="ppdb-print-actions">
        <button class="btn btn-accent" type="button" data-print-form>Cetak formulir ini</button>
        <a class="btn btn-ghost" href="{{ route('ppdb.printable.pdf') }}" data-download-pdf>Unduh formulir (PDF)</a>
        <a class="btn btn-ghost" href="{{ route('site.section', 'ppdb') }}#formulir">Isi formulir daring</a>
        <a class="btn btn-ghost" href="{{ route('site.section', 'ppdb') }}">Informasi PPDB</a>
      </div>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <article class="ppdb-print-sheet">
        {{-- Kop surat --}}
        <header class="ppdb-print-head">
          <img class="ppdb-print-logo" src="{{ \App\Support\SchoolSettings::logoUrl() }}" width="64" height="64" alt="">
          <div>
            <p class="ppdb-print-school">{{ $contact['name'] ?? 'SMK INFOKOM' }}</p>
            @if (filled($letterAddress))<p class="ppdb-print-address">{{ $letterAddress }}</p>@endif
            @php
                $letterContacts = collect([
                    filled($contact['phones']['general'] ?? null) ? 'Telp. '.$contact['phones']['general'] : null,
                    filled($contact['emails']['general'] ?? null) ? 'Email '.$contact['emails']['general'] : null,
                ])->filter()->implode(' · ');
            @endphp
            @if (filled($letterContacts))<p class="ppdb-print-address">{{ $letterContacts }}</p>@endif
          </div>
        </header>

        <h2 class="ppdb-print-title">FORMULIR PENDAFTARAN CALON MURID BARU</h2>
        @if (filled($spmb['period'] ?? null))<p class="ppdb-print-period">{{ $spmb['period'] }}</p>@endif
        <p class="ppdb-print-note">Isi kolom berikut dengan huruf cetak. Tanda kotak (☐) diberi centang pada pilihan yang sesuai.</p>

        {{-- A. Data calon murid --}}
        <section class="ppdb-print-group" aria-labelledby="printDataDiri">
          <h3 id="printDataDiri">A. Data Calon Murid</h3>
          <div class="ppdb-print-row"><span class="ppdb-print-label">Nama lengkap</span><span class="ppdb-print-line"></span></div>
          <div class="ppdb-print-grid">
            <div class="ppdb-print-row"><span class="ppdb-print-label">NISN</span><span class="ppdb-print-line"></span></div>
            <div class="ppdb-print-row"><span class="ppdb-print-label">NIK</span><span class="ppdb-print-line"></span></div>
          </div>
          <div class="ppdb-print-row"><span class="ppdb-print-label">Tempat, tanggal lahir</span><span class="ppdb-print-line"></span></div>
          <div class="ppdb-print-row">
            <span class="ppdb-print-label">Jenis kelamin</span>
            <span class="ppdb-print-options">
              <span class="ppdb-print-option"><span class="ppdb-print-box" aria-hidden="true"></span> Laki-laki</span>
              <span class="ppdb-print-option"><span class="ppdb-print-box" aria-hidden="true"></span> Perempuan</span>
            </span>
          </div>
          <div class="ppdb-print-row"><span class="ppdb-print-label">Asal sekolah</span><span class="ppdb-print-line"></span></div>
          <div class="ppdb-print-row"><span class="ppdb-print-label">Alamat</span><span class="ppdb-print-line"></span></div>
        </section>

        {{-- B. Pilihan program keahlian --}}
        <section class="ppdb-print-group" aria-labelledby="printProgram">
          <h3 id="printProgram">B. Pilihan Program Keahlian</h3>
          <p class="ppdb-print-subnote">Pilih satu program keahlian yang dituju.</p>
          @if ($programs->isNotEmpty())
            <div class="ppdb-print-programs">
              @foreach ($programs as $program)
                <span class="ppdb-print-option"><span class="ppdb-print-box" aria-hidden="true"></span> {{ $program->short }} · {{ $program->name }}</span>
              @endforeach
            </div>
          @else
            <p class="ppdb-print-subnote">Daftar program keahlian akan diumumkan panitia PPDB.</p>
          @endif
        </section>

        {{-- C. Data orang tua / wali --}}
        <section class="ppdb-print-group" aria-labelledby="printOrangTua">
          <h3 id="printOrangTua">C. Data Orang Tua / Wali</h3>
          <div class="ppdb-print-grid">
            <div class="ppdb-print-row"><span class="ppdb-print-label">Nama ayah</span><span class="ppdb-print-line"></span></div>
            <div class="ppdb-print-row"><span class="ppdb-print-label">Nama ibu</span><span class="ppdb-print-line"></span></div>
          </div>
          <div class="ppdb-print-grid">
            <div class="ppdb-print-row"><span class="ppdb-print-label">Nama wali (bila ada)</span><span class="ppdb-print-line"></span></div>
            <div class="ppdb-print-row"><span class="ppdb-print-label">Nomor telepon aktif</span><span class="ppdb-print-line"></span></div>
          </div>
          <div class="ppdb-print-row"><span class="ppdb-print-label">Alamat orang tua / wali</span><span class="ppdb-print-line"></span></div>
        </section>

        {{-- D. Kelengkapan dokumen --}}
        <section class="ppdb-print-group" aria-labelledby="printDokumen">
          <h3 id="printDokumen">D. Kelengkapan Dokumen</h3>
          @if ($requirements !== [])
            <div class="ppdb-print-programs">
              @foreach ($requirements as $requirement)
                <span class="ppdb-print-option"><span class="ppdb-print-box" aria-hidden="true"></span> {{ $requirement }}</span>
              @endforeach
            </div>
          @else
            <p class="ppdb-print-subnote">Daftar persyaratan dokumen diumumkan panitia PPDB. Konfirmasikan persyaratan terbaru kepada sekolah sebelum menyerahkan formulir ini.</p>
          @endif
        </section>

        {{-- E. Pernyataan & tanda tangan --}}
        <section class="ppdb-print-group" aria-labelledby="printPernyataan">
          <h3 id="printPernyataan">E. Pernyataan</h3>
          <p class="ppdb-print-statement">Saya menyatakan bahwa data yang saya isi di atas benar. Apabila di kemudian hari terbukti data tidak benar, saya bersedia menerima sanksi sesuai ketentuan yang berlaku di SMK INFOKOM.</p>
          <div class="ppdb-print-sign">
            <div class="ppdb-print-sign-place">
              <span class="ppdb-print-label">Tempat, tanggal</span>
              <span class="ppdb-print-line"></span>
            </div>
            <div class="ppdb-print-sign-box">
              <span>Calon murid,</span>
              <span class="ppdb-print-sign-line"></span>
            </div>
            <div class="ppdb-print-sign-box">
              <span>Orang tua / wali,</span>
              <span class="ppdb-print-sign-line"></span>
            </div>
          </div>
        </section>

        <footer class="ppdb-print-foot">
          <div>
            <span class="ppdb-print-label">Nomor pendaftaran (diisi panitia)</span>
            <span class="ppdb-print-line"></span>
          </div>
          <p>Formulir ini diserahkan ke panitia PPDB SMK INFOKOM bersama dokumen persyaratan. Pertanyaan pendaftaran dapat disampaikan melalui kontak sekolah pada halaman <a href="{{ route('site.section', 'ppdb') }}">Penerimaan murid baru</a>.</p>
        </footer>
      </article>

      <div class="ppdb-print-actions ppdb-print-actions-bottom">
        <button class="btn btn-brand" type="button" data-print-form>Cetak formulir ini</button>
        <a class="btn btn-line" href="{{ route('ppdb.printable.pdf') }}" data-download-pdf>Unduh formulir (PDF)</a>
        <a class="btn btn-line" href="{{ route('site.section', 'ppdb') }}">Kembali ke informasi PPDB</a>
      </div>
    </div>
  </section>
@endsection
