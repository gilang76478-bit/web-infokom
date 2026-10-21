<!doctype html>
{{-- Lembar PDF formulir PPDB untuk tombol "Unduh PDF".
     Dirender dompdf, sehingga tata letaknya memakai tabel dan gaya
     inline (tanpa flexbox/grid) plus font DejaVu bawaan dompdf yang
     mendukung karakter kotak centang ☐. Berkas ini berdiri sendiri
     tanpa tata letak situs dan tanpa aset eksternal agar PDF selalu
     terbentuk utuh bahkan tanpa koneksi jaringan. --}}
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Formulir Pendaftaran Calon Murid Baru</title>
  <style>
      @page { size: A4; margin: 16mm 14mm; }

      body {
          font-family: 'DejaVu Sans', sans-serif;
          font-size: 10.5pt;
          color: #111;
      }

      .head { border-bottom: 3px double #111; padding-bottom: 8px; margin-bottom: 14px; }
      .head td { vertical-align: middle; }
      .head img { width: 58px; height: 58px; }
      .school { font-size: 14pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin: 0; }
      .address { font-size: 8.5pt; color: #333; margin: 2px 0 0; line-height: 1.45; }

      .title { text-align: center; font-size: 12.5pt; font-weight: bold; margin: 0; }
      .period { text-align: center; font-weight: bold; margin: 2px 0 0; }
      .note { font-size: 8.5pt; color: #333; margin: 10px 0 0; }

      .group { margin-top: 16px; page-break-inside: avoid; }
      .group h2 {
          font-size: 10.5pt;
          margin: 0 0 6px;
          padding: 3px 6px;
          background: #eef1fa;
          border-left: 4px solid #2a3fe0;
      }
      .subnote { font-size: 8.5pt; color: #333; margin: 0 0 6px; }

      table.rows { width: 100%; border-collapse: collapse; }
      table.rows td { padding: 4px 8px 4px 0; vertical-align: bottom; }
      td.label { width: 175px; font-weight: bold; }
      td.line { border-bottom: 1px solid #111; height: 22px; }

      table.options { width: 100%; border-collapse: collapse; }
      table.options td { padding: 3px 0; }

      .sign td { vertical-align: top; padding: 0 6px; }
      .sign .place { padding-top: 18px; }
      .sign .box { text-align: center; padding-top: 44px; }
      .sign .sigline { border-bottom: 1px solid #111; margin: 0 18px; height: 24px; }

      .foot { margin-top: 18px; border-top: 1px solid #bbb; padding-top: 8px; font-size: 8.5pt; color: #333; }
      .foot p { margin: 4px 0 0; line-height: 1.5; }
  </style>
</head>
<body>
  @php
      // Alamat kop surat disusun dari pengaturan kontak; hanya baris yang
      // terisi yang digabung agar tidak muncul koma kosong.
      $letterAddress = collect($contact['address'] ?? [])->filter()->implode(', ');
      $letterContacts = collect([
          filled($contact['phones']['general'] ?? null) ? 'Telp. '.$contact['phones']['general'] : null,
          filled($contact['emails']['general'] ?? null) ? 'Email '.$contact['emails']['general'] : null,
      ])->filter()->implode(' · ');
      $requirements = $spmb['requirements'] ?? [];
  @endphp

  {{-- Kop surat --}}
  <table class="head">
    <tr>
      @php($schoolLogoPath = \App\Support\SchoolSettings::logoPath())
      <td style="width: 70px;"><img src="{{ $schoolLogoPath ? public_path('storage/'.$schoolLogoPath) : public_path('images/logo-192.png') }}" alt=""></td>
      <td>
        <p class="school">{{ $contact['name'] ?? 'SMK INFOKOM' }}</p>
        @if (filled($letterAddress))<p class="address">{{ $letterAddress }}</p>@endif
        @if (filled($letterContacts))<p class="address">{{ $letterContacts }}</p>@endif
      </td>
    </tr>
  </table>

  <p class="title">FORMULIR PENDAFTARAN CALON MURID BARU</p>
  @if (filled($spmb['period'] ?? null))<p class="period">{{ $spmb['period'] }}</p>@endif
  <p class="note">Isi kolom berikut dengan huruf cetak. Tanda kotak (&#9744;) diberi centang pada pilihan yang sesuai.</p>

  {{-- A. Data calon murid --}}
  <div class="group">
    <h2>A. Data Calon Murid</h2>
    <table class="rows">
      <tr><td class="label">Nama lengkap</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">NISN</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">NIK</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Tempat, tanggal lahir</td><td class="line">&nbsp;</td></tr>
    </table>
    <table class="options">
      <tr>
        <td class="label" style="vertical-align: top; padding-top: 4px;">Jenis kelamin</td>
        <td>&#9744;&nbsp; Laki-laki</td>
        <td>&#9744;&nbsp; Perempuan</td>
      </tr>
    </table>
    <table class="rows">
      <tr><td class="label">Asal sekolah</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Alamat</td><td class="line">&nbsp;</td></tr>
    </table>
  </div>

  {{-- B. Pilihan program keahlian --}}
  <div class="group">
    <h2>B. Pilihan Program Keahlian</h2>
    <p class="subnote">Pilih satu program keahlian yang dituju.</p>
    @if ($programs->isNotEmpty())
      <table class="options">
        @foreach ($programs as $program)
          <tr><td style="width: 20px;">&#9744;</td><td>{{ $program->short }} &middot; {{ $program->name }}</td></tr>
        @endforeach
      </table>
    @else
      <p class="subnote">Daftar program keahlian akan diumumkan panitia PPDB.</p>
    @endif
  </div>

  {{-- C. Data orang tua / wali --}}
  <div class="group">
    <h2>C. Data Orang Tua / Wali</h2>
    <table class="rows">
      <tr><td class="label">Nama ayah</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Nama ibu</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Nama wali (bila ada)</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Nomor telepon aktif</td><td class="line">&nbsp;</td></tr>
      <tr><td class="label">Alamat orang tua / wali</td><td class="line">&nbsp;</td></tr>
    </table>
  </div>

  {{-- D. Kelengkapan dokumen --}}
  <div class="group">
    <h2>D. Kelengkapan Dokumen</h2>
    @if ($requirements !== [])
      <table class="options">
        @foreach ($requirements as $requirement)
          <tr><td style="width: 20px;">&#9744;</td><td>{{ $requirement }}</td></tr>
        @endforeach
      </table>
    @else
      <p class="subnote">Daftar persyaratan dokumen diumumkan panitia PPDB. Konfirmasikan persyaratan terbaru kepada sekolah sebelum menyerahkan formulir ini.</p>
    @endif
  </div>

  {{-- E. Pernyataan & tanda tangan --}}
  <div class="group">
    <h2>E. Pernyataan</h2>
    <p style="line-height: 1.6; margin: 0 0 8px;">Saya menyatakan bahwa data yang saya isi di atas benar. Apabila di kemudian hari terbukti data tidak benar, saya bersedia menerima sanksi sesuai ketentuan yang berlaku di {{ $contact['name'] ?? 'SMK INFOKOM' }}.</p>
    <table class="sign" width="100%">
      <tr>
        <td class="place" width="34%">
          <table class="rows" style="width: 100%;">
            <tr><td class="label" style="width: 95px;">Tempat, tanggal</td><td class="line">&nbsp;</td></tr>
          </table>
        </td>
        <td class="box" width="33%">
          <div>Calon murid,</div>
          <div class="sigline">&nbsp;</div>
        </td>
        <td class="box" width="33%">
          <div>Orang tua / wali,</div>
          <div class="sigline">&nbsp;</div>
        </td>
      </tr>
    </table>
  </div>

  <div class="foot">
    <table class="rows" style="width: 60%;">
      <tr><td class="label">Nomor pendaftaran (diisi panitia)</td><td class="line">&nbsp;</td></tr>
    </table>
    <p>Formulir ini diserahkan ke panitia PPDB {{ $contact['name'] ?? 'SMK INFOKOM' }} bersama dokumen persyaratan. Versi daring formulir ini tersedia pada halaman Penerimaan Murid Baru: {{ route('ppdb.printable') }}</p>
  </div>
</body>
</html>
